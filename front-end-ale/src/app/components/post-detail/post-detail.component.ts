import { Component } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { PostService } from '../../services/post.service';
import { global } from '../../services/global';


@Component({
  selector: 'app-post-detail',
  templateUrl: './post-detail.component.html',
  styleUrl: './post-detail.component.css',
  providers: [PostService]
})
export class PostDetailComponent {
  public page_title: string;
  public post: any;
  public url: string;

  constructor(
    private _postService: PostService,
    private _route: ActivatedRoute,
    private _router: Router
  ) {
    this.page_title = '';
    this.url = global.url;
    this.post = null;
  }

  ngOnInit() {
    this.getPost();
  }

  getPost() {
    this._route.params.subscribe(
      params => {
        let id = +params['id'];
        this._postService.getPost(id).subscribe({
          next: (response) => {
            if (response.status == 'success') {
              this.post = response.post;
              this.page_title = this.post.title;
            } else {
              this._router.navigate(['inicio']);
            }
          },
          error: (error) => {
            console.log(<any>error);
          }
        })
      });
  }
}
