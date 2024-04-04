import { Component } from '@angular/core';
import { UserService } from '../../services/user.service';
import { PostService } from '../../services/post.service';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { global } from '../../services/global';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrl: './home.component.css',
  providers: [
    UserService,
    PostService
  ]
})
export class HomeComponent {
  public page_title: string;
  public url: string;
  public identity: any;
  public token: any;
  public posts: any;
  public time: any;


  constructor(
    private _postService: PostService,
    private _userService: UserService,
    private _router: Router,
    private _route: ActivatedRoute
  ) {
    this.page_title = "Inicio"
    this.url = global.url;
    this.identity = this._userService.getIdentity();
    this.token = this._userService.getToken();
  }

  ngOnInit(): void {
    this.getPosts();
    this.deletePost();
    this.getCurrentDate()
  }

  getCurrentDate() {
    setInterval(() => {
      this.time = new Date();
    },1000);
  }

  getPosts() {
    this._postService.getPosts().subscribe({
      next: (response) => {
        this.posts = response.posts;
      },
      error: (error) => {
        console.log(<any>error);
      }
    })
  }

  deletePost() {
    this._route.params.subscribe(params => {
      let destroyPost = +params['delete'];
      let id = +params['id'];
      if (destroyPost == 1) {
        this._postService.destroy(this.token, id).subscribe({
          next: (response) => {
            if (response.status == 'success') {
              console.log(response);
            }
          },
          error: (error) => {
            console.log(<any>error);
          }
        })
        this._router.navigate(['inicio']);
      }
    })
  }
}
