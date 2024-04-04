import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { IdentityGuardsService } from './services/identity.guards.service';

// IMPORTAR LOS COMPONENTES
import { LoginComponent } from './components/login/login.component';
import { RegisterComponent } from './components/register/register.component';
import { HomeComponent } from './components/home/home.component';
import { ErrorComponent } from './components/error/error.component';
import { UserEditComponent } from './components/user-edit/user-edit.component';
import { CategoryNewComponent } from './components/category-new/category-new.component';
import { PostNewComponent } from './components/post-new/post-new.component';
import { PostDetailComponent } from './components/post-detail/post-detail.component';
import { PostEditComponent } from './components/post-edit/post-edit.component';
import { CategoryDetailComponent } from './components/category-detail/category-detail.component';

const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'inicio', component: HomeComponent },
  { path: 'login', component: LoginComponent },
  { path: 'logout/:sure', component: LoginComponent },
  { path: 'registro', component: RegisterComponent },
  { path: 'ajustes', component: UserEditComponent, canActivate: [IdentityGuardsService] },
  { path: 'crear-categoria', component: CategoryNewComponent, canActivate: [IdentityGuardsService] },
  { path: 'crear-entrada', component: PostNewComponent, canActivate: [IdentityGuardsService] },
  { path: 'entrada/:id', component: PostDetailComponent },
  { path: 'editar-entrada/:id', component: PostEditComponent, canActivate: [IdentityGuardsService] },
  { path: 'borrar-entrada/:delete/:id', component: HomeComponent, canActivate: [IdentityGuardsService] },
  { path: 'categoria/:id', component: CategoryDetailComponent },
  { path: '**', component: ErrorComponent }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
