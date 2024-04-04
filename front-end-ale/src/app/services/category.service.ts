import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { global } from './global';

@Injectable({
  providedIn: 'root'
})

export class CategoryService {
  public url: string;
  public identity: any;
  public token: any;

  constructor(
    public _http: HttpClient
  ) {
    this.url = global.url;
  }

  create(token: any, category: any): Observable<any> {

    let json = JSON.stringify(category);
    let params = 'json=' + json;

    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded').set('Authorization', token);

    return this._http.post(this.url + 'category', params, { headers: headers });
  }

  getCategory(id: number): Observable<any> {
    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');

    return this._http.get(this.url + 'category/' + id, { headers: headers });
  }

  getPosts(id: number): Observable<any> {
    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');

    return this._http.get(this.url + 'post/category/' + id, { headers: headers });
  }

  getCategories(): Observable<any> {
    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');

    return this._http.get(this.url + 'category', { headers: headers });
  }

  update(token: any, user: any): Observable<any> {

    let json = JSON.stringify(user);
    let params = 'json=' + json;

    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded').set('Authorization', token);

    return this._http.put(this.url + 'user/update', params, { headers: headers });
  }

}
