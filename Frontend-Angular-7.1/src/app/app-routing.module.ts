import { NgModule } from '@angular/core';
import { RouterModule, Routes }  from '@angular/router';

import { MainComponent } from './main/main.component';
import { RedirectComponent } from './redirect/redirect.component';

const appRoutes: Routes = [
  { path: '', component: MainComponent},
  { path: '**', component: RedirectComponent }
];

@NgModule({
  declarations: [],
  imports: [
    RouterModule.forRoot(
      appRoutes
    )
  ],
  exports: [
    RouterModule
  ]
})
export class AppRoutingModule { }
