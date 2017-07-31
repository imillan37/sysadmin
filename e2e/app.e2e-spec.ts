import { BniPage } from './app.po';

describe('bni App', () => {
  let page: BniPage;

  beforeEach(() => {
    page = new BniPage();
  });

  it('should display welcome message', () => {
    page.navigateTo();
    expect(page.getParagraphText()).toEqual('Welcome to app!');
  });
});
