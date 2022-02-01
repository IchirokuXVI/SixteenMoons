import { Component, OnInit } from '@angular/core';
import { UsersService } from "../../services/users.service";
import { User } from "../../models/user.module";
import { FormControl, FormGroup } from "@angular/forms";

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent implements OnInit {
  public frm: FormGroup;
  private user: User;
  public token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  constructor(private _usersService: UsersService) {
    this.frm = new FormGroup({
      _token: new FormControl(),
      username: new FormControl(),
      email: new FormControl(),
      password: new FormControl()
    });
  }

  ngOnInit(): void {
  }

  public submit(frm: FormGroup) {
    this._usersService.store(frm.value).subscribe(resp => {
      console.log(resp);
    });
  }
}
