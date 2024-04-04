import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { User } from '../models/user';
import { global } from './global';

@Injectable({
  providedIn: 'root'
})
export class UserService {
  public url: string;
  public identity: any;
  public token: any;

  constructor(
    public _http: HttpClient
  ) {
    this.url = global.url;
  }

  register(user: any): Observable<any> {

    let json = JSON.stringify(user);
    let params = 'json=' + json;

    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');

    return this._http.post(this.url + 'register', params, { headers: headers });
  }

  signup(user: any, gettoken: any = null): Observable<any> {
    if (gettoken != null) {
      user.getToken = true;
    }
    let json = JSON.stringify(user);
    let params = 'json=' + json;

    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');

    return this._http.post(this.url + 'login', params, { headers: headers });
  }

  update(token: any, user: any): Observable<any> {

    let json = JSON.stringify(user);
    let params = 'json=' + json;

    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded').set('Authorization', token);

    return this._http.put(this.url + 'user/update', params, { headers: headers });
  }

  uploadImage(token: any, file: File): Observable<any> {
    let params = new FormData();
    params.append('file0', file);

    let headers = new HttpHeaders().set('Authorization', token);
    return this._http.post(this.url + 'user/upload', params, { headers: headers });
  }

  getIdentity() {
    let identity = JSON.parse(localStorage.getItem('identity')!);

    if (identity && identity != "undefined") {
      this.identity = identity;
    } else {
      this.identity = null;
    }

    return this.identity;
  }

  getToken() {
    let token = localStorage.getItem('token')!;

    if (token && token != "undefined") {
      this.token = token;
    } else {
      this.token = null;
    }

    return this.token;
  }
}
