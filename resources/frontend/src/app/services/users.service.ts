import { Injectable } from '@angular/core';
import {HttpClient, HttpHeaders} from "@angular/common/http";
import {User} from "../models/user.module";

@Injectable({
  providedIn: 'root'
})


export class UsersService {
  private readonly url;
  private readonly headers;

  constructor(private http: HttpClient) {
    this.url = 'http://localhost/api';
    this.headers = new HttpHeaders({'Content-Type': 'application/json'});
  }

  public store(user: User) {
    return this.http.post(`${this.url}/users`, user, this.headers);
  }
}
