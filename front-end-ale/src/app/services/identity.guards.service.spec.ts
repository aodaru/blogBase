import { TestBed } from '@angular/core/testing';

import { IdentityGuardsService } from './identity.guards.service';

describe('IdentityGuardsService', () => {
  let service: IdentityGuardsService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(IdentityGuardsService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
