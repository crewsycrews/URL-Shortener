import { Component, OnInit, Inject } from '@angular/core';
import { DOCUMENT } from '@angular/common';
import { ApiService } from '../api.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-redirect',
  template: '<p> {{ Message }} </p>',
  styles: ['p { font-size: 3.3em; }']
})

export class RedirectComponent implements OnInit {
  Message: string = "Redirecting...";
  public href: string = "";
  constructor(private api: ApiService, @Inject(DOCUMENT) private document: any, private router: Router) { 
    this.href = this.router.url;
    this.redirect(this.href);
  }
  redirect(uri: string) {
    this.api.getUri(uri).subscribe(value => {
      this.document.location = 'http://' + value.body.Success;
    },
      error => {
        this.Message = '404 - Not found';
      }
    )
  }
  ngOnInit() {
    // this.Message = "Redirecting...";
  }

}
