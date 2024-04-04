import { Component } from '@angular/core';
import { User } from '../../models/user';
import { UserService } from '../../services/user.service';
import { Router, ActivatedRoute, Params } from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrl: './login.component.css',
  providers: [UserService]
})
export class LoginComponent {
  public page_title: string;
  public user: User;
  public status: string;
  public identity: any;
  public token: any;

  constructor(
    private _userService: UserService,
    private _router: Router,
    private _route: ActivatedRoute
  ) {
    this.page_title = "Identificate"
    this.user = new User(1, '', '', 'ROLE_USER', '', '', '', '');
    this.status = "";
    this.token = "";
  }

  ngOnInit() {
    // Se ejecuta siempre que se cargue el componente y cierra la sesion si le llega el 1
    this.logout();
  }

  onSubmit(form: any) {

    // OBTENIENDO TOKEN DEL USUARIO IDENTIFICADO
    this._userService.signup(this.user).subscribe({
      next: (response) => {
        if (response.status != 'error') {
          this.status = 'success';
          this.token = response.message;

          // OBTENIENDO OBJETO DEL USUARIO IDENTIFICADO
          this._userService.signup(this.user, true).subscribe({
            next: (response) => {
              this.identity = response.message;

              // ALMACENANDO TOKEN E IDENTITY EN EL LOCAL STORAGE
              localStorage.setItem('token', this.token)
              localStorage.setItem('identity', JSON.stringify(this.identity));

              // REDIRECCIONAR AL INICIO
              this._router.navigate(['inicio']);
            },
            error: (error) => {
              this.status = 'error';
              console.log(<any>error);
            }
          });
        } else {
          this.status = 'error';
        }
      },
      error: (error) => {
        this.status = 'error';
        console.log(<any>error);
      }
    });

  }

  logout() {
    this._route.params.subscribe(params => {
      let logout = +params['sure'];

      if (logout == 1) {
        localStorage.removeItem('identity');
        localStorage.removeItem('token');

        this.identity = null;
        this.token = null;

        // REDIRECCION A LA PAGINA PRINCIPAL
        this._router.navigate(['inicio']);
      }
    })

  }

}
