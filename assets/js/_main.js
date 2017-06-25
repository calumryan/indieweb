var page = document.querySelector('body');page.className += 'js ';

var root = document.querySelector('body'),
    js_menu_1 = document.getElementById('js-menu-1'),
    js_menu_2 = document.getElementById('js-menu-2'),
    html = document.querySelector('html');

function hasClass(element, cls) {
    return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1;
};

function toggleMenu(){
  if ( hasClass(root, 'body-state--open') ) {
    root.classList.remove('body-state--open');
  } else {
    root.classList.add('body-state--open');
  }
};

js_menu_1.onclick = function(event) {
  event.preventDefault();
  toggleMenu();
};
