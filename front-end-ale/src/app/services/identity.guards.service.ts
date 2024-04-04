import { Injectable } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { UserService } from './user.service';

@Injectable({
  providedIn: 'root'
})
export class IdentityGuardsService {

  constructor(
    private _router: Router,
    private _userService: UserService
  ) { }

  canActivate(): boolean {
    let identity = this._userService.getIdentity();
    if (identity) {
      return true;
    } else {
      this._router.navigate(['/inicio']);
      return false;
    }
  }
}

