import { Component, OnInit, Inject } from '@angular/core';
import { ApiService } from '../api.service';
import {DOCUMENT} from '@angular/platform-browser';
import { environment } from '../../environments/environment';

@Component({
  selector: 'app-main',
  templateUrl: './main.component.html',
  styleUrls: ['./main.component.sass']
})
export class MainComponent implements OnInit {
  title = 'Crews URL Shortener';
  url: string;
  custom_uri: string;
  generatedUri: string;
  error: string;
  validClass: string;
  appUrl: string;
  constructor(private api: ApiService, @Inject(DOCUMENT) private document) {
    if(environment.devPort){
      this.appUrl = this.document.location.protocol +'//'+ this.document.location.hostname + ':' + environment.devPort;
    } else {
      this.appUrl = this.document.location.protocol +'//'+ this.document.location.hostname;
    }
  }

  generateUri(url: string, custom_uri?: string) {
    this.generatedUri = null;
    this.error = null;
    this.api.generate(url, custom_uri).subscribe(value => {
      this.generatedUri = value.body.uri;
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
  
  ngOnInit() {
  }

}
