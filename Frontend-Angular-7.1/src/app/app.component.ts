import { Component } from '@angular/core';
import { ApiService } from './api.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.sass']
})
export class AppComponent {
  title = 'Crews URL Shortener';
  url: string;
  custom_uri: string;
  generatedUrl: string;
  error: string;
  validClass: string;
  constructor(private api: ApiService) { }

  generateUri(url: string, custom_uri?: string) {
    this.generatedUrl = null;
    this.error = null;
    this.api.generate(url, custom_uri).subscribe(value => {
      this.generatedUrl = value.body.GeneratedUrl;
    },
      error => {
        this.error = error.error.Error;
      });;
  }
  validateUri(uri: string) {
    if (!uri) {
      this.validClass = '';
      return;
    }
    this.api.getUri(uri).subscribe(value => {
      this.validClass = 'is-error';
    },
      error => {
        this.validClass = 'is-success';
      }
    )
  }
}
