import { Component } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { UserService } from '../../services/user.service';
import { CategoryService } from '../../services/category.service';
import { PostService } from '../../services/post.service';
import { Post } from '../../models/post';
import { Category } from '../../models/category';
import { global } from '../../services/global';

@Component({
  selector: 'app-post-new',
  templateUrl: './post-new.component.html',
  styleUrl: './post-new.component.css',
  providers: [
    UserService,
    CategoryService,
    PostService
  ]
})

export class PostNewComponent {
  public page_title: string;
  public identity: any;
  public token: any;
  public post: Post;
  public status: string;
  public categories: any;
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
    private _route: ActivatedRoute,
    private _router: Router,
    private _userService: UserService,
    private _categoryService: CategoryService,
    private _postService: PostService
  ) {
    this.page_title = "Crea Una Entrada"
    this.identity = _userService.getIdentity();
    this.token = _userService.getToken();
    this.filename = '';
    this.url = global.url;
    this.post = new Post(1, this.identity.sub, 1, '', '', '', null);
    this.status = '';
  }

  ngOnInit() {
    this.getCategories();
  }

  onSubmit(form: any) {
    this._postService.create(this.token, this.post).subscribe({
      next: (response) => {
        if (response.status == "success") {
          this.status = response.status;
          form.reset();
          this.filename = ''
        } else {
          this.status = "error";
        }
      },
      error: (error) => {
        this.status = "error";
        console.log(<any>error);
      }
    });
  }

  getCategories() {
    this._categoryService.getCategories().subscribe({
      next: (response) => {
        if (response.status == 'success') {
          console.log(response)
          this.categories = response.categories;
        }
      },
      error: (error) => {
        console.log(<any>error);
      }
    })
  }

  onFileSelected(event: any) {
    const file: File = event.target.files[0];
    if (file) {
      this._postService.uploadImage(this.token, file).subscribe({
        next: (response) => {
          console.log(response);
          this.post.image = response.image;
          this.filename = response.image;
        },
        error: (error) => {
          console.log(<any>error)
        }
      })
    }
  }
}
