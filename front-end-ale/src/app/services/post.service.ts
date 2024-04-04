import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { User } from '../models/user';
import { global } from './global';

@Injectable({
  providedIn: 'root'
})
export class PostService {
  public url: string;
  public identity: any;
  public token: any;

  constructor(
    public _http: HttpClient
  ) {
    this.url = global.url;
  }

  create(token: any, post: any): Observable<any> {

    let json = JSON.stringify(post);
    let params = 'json=' + json;

    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded').set('Authorization', token);

    return this._http.post(this.url + 'post', params, { headers: headers });
  }

  update(token: any, post: any, id: number): Observable<any> {

    let json = JSON.stringify(post);
    let params = 'json=' + json;

    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded').set('Authorization', token);

    return this._http.put(this.url + 'post/' + id, params, { headers: headers });
  }

  destroy(token: any, id: number): Observable<any> {
    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded').set('Authorization', token);

    return this._http.delete(this.url + 'post/' + id, { headers: headers });
  }

  uploadImage(token: any, file: File): Observable<any> {
    let params = new FormData();
    params.append('file0', file);

    let headers = new HttpHeaders().set('Authorization', token);
    return this._http.post(this.url + 'post/upload', params, { headers: headers });
  }

  getPosts(): Observable<any> {
    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');

    return this._http.get(this.url + 'post', { headers: headers });
  }

  getPost(id: number): Observable<any> {

    let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');

    return this._http.get(this.url + 'post/' + id, { headers: headers });
  }
}
