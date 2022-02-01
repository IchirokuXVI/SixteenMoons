<?php

namespace App\Http\Controllers;

use App\Course;
use App\CustomRole;
use Illuminate\Http\Request;
use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Amount;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class PaymentController extends Controller
{
    private $api_context;
    /**
     ** We declare the Api context as above and initialize it in the contructor
     **/
    public function __construct()
    {
        $this->api_context = new ApiContext(
            new OAuthTokenCredential(config('paypal.client_id'), config('paypal.secret'))
        );
        $this->api_context->setConfig(config('paypal.settings'));
    }

    public function create(Course $course) {
        return view('payment.create', ['course' => $course]);
    }

    public function success(Course $course) {
        return view('payment.success', ['course' => $course]);
    }

    public function store(Request $request, Course $course, CustomRole $customRole) {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($customRole->name)
            ->setCurrency('EUR')
            ->setQuantity('1')
            ->setPrice($customRole->price);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $amount = new Amount();
        $amount->setCurrency('EUR')
            ->setTotal($customRole->price);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription('Support a course and gain privileges on the course');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('payment.confirm', $course))
            ->setCancelUrl(route('payment.confirm', $course));

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        try {
            $payment->create($this->api_context);
        } catch(PayPalConnectionException $ex) {
            $request->session()->flash('error', 'Some error occur, sorry for inconvenient');
            return redirect()->route('payment.create', $course);
        }

        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirectUrl = $link->getHref();
                break;
            }
        }

        /** add payment ID to session **/
        $request->session()->put('paypal_payment_id', $payment->getId());
        if (isset($redirectUrl)) {
            /** redirect to paypal **/
            return redirect()->away($redirectUrl);
        }
        $request->session()->flash('error', 'Unknown error occurred');
        return redirect()->route('payment.create', $course);
    }

    public function confirm(Request $request, Course $course) {
        $paymentId = $request->session()->pull('paypal_payment_id');
        if (empty(request()->PayerID) || empty(request()->token)) {
            $request->session()->flash('error', 'Payment failed');
            return redirect()->route('payment.create', $course);
        }

        $payment = Payment::get($paymentId, $this->api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId(request()->PayerID);

        // Execute the payment
        $result = $payment->execute($execution, $this->api_context);

        $roleName = $result->getTransactions()[0]->getItemList()->getItems()[0]->name;
        if ($result->getState() == 'approved') {
            $request->session()->flash('success', 'Payment success');
            auth()->user()->customRoles()->attach(CustomRole::where('course_id', $course->id)->where('name', $roleName)->first(), ['supporter' => true]);
            return redirect()->route('payment.success', $course);
        } else {
            $request->session()->flash('error', 'Payment failed');
            return redirect()->route('payment.create', $course);
        }
    }
}
