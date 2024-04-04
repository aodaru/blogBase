import { Component } from '@angular/core';
import { User } from '../../models/user';
import { UserService } from '../../services/user.service';
import { HttpClient } from '@angular/common/http';
import { global } from '../../services/global';

@Component({
  selector: 'app-user-edit',
  templateUrl: './user-edit.component.html',
  styleUrl: './user-edit.component.css',
  providers: [UserService]
})

export class UserEditComponent {
  public page_title: string;
  public user: User;
  public identity: any;
  public token: any;
  public status: string;
  public filename: string;
  public url: string;

  public options: Object = {
    charCounterCount: true,
    toolbarButtons: ['bold', 'italic', 'underline', 'paragraphFormat', 'alert'],
    toolbarButtonsXS: ['bold', 'italic', 'underline', 'paragraphFormat', 'alert'],
    toolbarButtonsSM: ['bold', 'italic', 'underline', 'paragraphFormat', 'alert'],
    toolbarButtonsMD: ['bold', 'italic', 'underline', 'paragraphFormat', 'alert'],
  };

  constructor(
    private _userService: UserService,
    private http: HttpClient
  ) {
    this.page_title = "Ajustes de usuario";
    this.user = new User(1, '', '', 'ROLE_USER', '', '', '', '');
    this.identity = _userService.getIdentity();
    this.token = _userService.getToken();
    this.filename = this.identity.image;
    this.status = "";
    this.url = global.url;
    // RELLENAR OBJETO DE USUARIO
    this.user = new User(
      this.identity.sub,
      this.identity.name,
      this.identity.surname,
      this.identity.role,
      this.identity.email,
      '',
      this.identity.description,
      this.identity.image
    );
  }

  onSubmit(form: any) {
    this._userService.update(this.token, this.user).subscribe({
      next: (response) => {
        if (response.status == 'success') {
          this.status = 'success';
          // Actualizar la sesion
          if (this.user.name != response.changes.name) {
            this.user.name = response.changes.name;
          }
          if (this.user.surname != response.changes.surname) {
            this.user.surname = response.changes.surname;
          }
          if (this.user.email != response.changes.email) {
            this.user.email = response.changes.email;
          }
          if (this.user.description != response.changes.description) {
            this.user.description = response.changes.description;
          }
          if (this.user.image != response.changes.image) {
            this.user.image = response.changes.image;
          }
          this.identity = this.user;
          localStorage.setItem('identity', JSON.stringify(this.identity));
        }

      },
      error: (error) => {
        this.status = "error";
        console.log(<any>error);
      }
    })
  }

  onFileSelected(event: any) {
    const file: File = event.target.files[0];
    if (file) {
      this.filename = file.name;
      this._userService.uploadImage(this._userService.getToken(), file).subscribe({
        next: (response) => {
          this.user.image = response.image;
          this.identity.image = this.user.image;
          this.filename = this.identity.image;
        },
        error: (error) => {
          console.log(<any>error)
        }
      })
    }
  }

}
