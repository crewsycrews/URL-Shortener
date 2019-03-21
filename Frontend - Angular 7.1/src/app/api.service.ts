import { Injectable } from '@angular/core';
import { HttpClient, HttpResponse, HttpEventType } from '@angular/common/http';
import { Observable, throwError, Subject } from 'rxjs';
import { catchError, retry } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class ApiService {

  url = 'http://localhost:8000/';
  apiUrl = this.url + 'api/';
  generateUrl = this.apiUrl + "generate";

  constructor(private http: HttpClient) { }

  getMain(): Observable<any> {
    return this.http.get(
      this.apiUrl, { observe: 'response' });
  }
  generate(url: string, custom_uri: string): Observable<any> {
    return this.http.post(
      this.generateUrl, {'url': url, 'custom_uri': custom_uri},{ observe: 'response' });
  }
  getUri(uri: string): Observable<any> {
    return this.http.get(
      this.apiUrl + uri, { observe: 'response' });
  }
}
