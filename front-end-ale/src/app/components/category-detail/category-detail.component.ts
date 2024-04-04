import { Component } from '@angular/core';
import { CategoryService } from '../../services/category.service';
import { PostService } from '../../services/post.service';
import { UserService } from '../../services/user.service';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { global } from '../../services/global';

@Component({
  selector: 'app-category-detail',
  templateUrl: './category-detail.component.html',
  styleUrl: './category-detail.component.css',
  providers: [
    UserService,
    PostService,
    CategoryService
  ]
})
export class CategoryDetailComponent {
  public page_title: string;
  public url: string;
  public identity: any;
  public token: any;
  public posts: any;
  public category: any;

  constructor(
    private _categoryService: CategoryService,
    private _userService: UserService,
    private _postService: PostService,
    private _router: Router,
    private _route: ActivatedRoute
  ) {
    this.page_title = '';
    this.url = global.url;
  }

  ngOnInit(): void {
    this.getPosts();
    this.deletePost();
  }

  getPosts() {
    this._route.params.subscribe(params => {
      let id = +params['id'];
      this._categoryService.getPosts(id).subscribe({
        next: (response) => {
          this.posts = response.posts;
          this._categoryService.getCategory(id).subscribe({
            next: (response) => {
              this.category = response.category;
              this.page_title = "Categoria de " + this.category.name;
            }
          })
        },
        error: (error) => {
          console.log(<any>error);
          this._router.navigate(['inicio']);
        }
      })
    });
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
