PyroCMS Foundation 4 Navigation
===============================

## Introduction
This is a plugin to output the PyroCMS menu using markup friendly to Foundation 4. For simplicity, most options of the original plugin have been removed. It supports an abritary depth of submenus.

Suggestions and improvements are welcome.

## Usage
Drop the `navbar.php` file into your `addons/shared_addons/plugins/` directory. Use `navbar:left` or `navbar:right` to enclose the links in a `ul.left` or `ul.right`. Use `navbar:links` to get just the li's.

```html
<nav id="header-nav" class="top-bar" role="navigation">
  <ul class="title-area">
    <li class="name">
      <h1><a href="{{ base_url }}" class="navbar-brand">{{ settings:site_name }}</a></h1>
    </li>
    <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
  </ul>

  <section class="top-bar-section">
    {{ navbar:left group="header" }}
  </section>
</nav>
```