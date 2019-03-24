import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { HttpClientModule } from '@angular/common/http';
import { ClipboardModule } from 'ngx-clipboard';
import { AppComponent } from './app.component';
import { RedirectComponent } from './redirect/redirect.component';
import { MainComponent } from './main/main.component';
import { AppRoutingModule } from './app-routing.module';


@NgModule({
  declarations: [
    AppComponent,
    RedirectComponent,
    MainComponent
  ],
  imports: [
    BrowserModule,
    HttpClientModule,
    ClipboardModule,
    AppRoutingModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
