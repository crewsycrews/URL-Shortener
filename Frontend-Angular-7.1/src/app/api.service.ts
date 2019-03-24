import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';


@Injectable({
  providedIn: 'root'
})
export class ApiService {
  apiUrl = environment.api;
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
