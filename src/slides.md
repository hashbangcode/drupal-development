---
theme: uncover
paginate: false
class:
  - lead
  - invert
size: 16:9
footer: "Philip Norton [hashbangcode.com](https://www.hashbangcode.com) [@hashbangcode](https://twitter.com/hashbangcode) [@philipnorton42](https://twitter.com/philipnorton42)"
marp: true
---

# Drupal Development

---
## Drupal Development
- Extending Drupal's functionalty to do whatever you want it to.
- Covers Modules, Themes, Install Profiles.

---
# Setting Up Drupal For Development

---
## Setting Up Drupal
- Lots of options exist to ease development in Drupal.
- This includes turning off the Drupal cache, forcing autodiscovery on every page load and preventing permission hardening. 

---
## Setting Up Drupal
- Drupal has a example.settings.local.php file.
- Copy this to settings.local.php.
- Uncomment the following from the bottom of the settings.php file.

```php
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
```

---
## Setting Up Drupal
- The settings.local.php file will also include a development.services.yml file.
- This turns on cacheability headers and turns allows backend cache classes to be pucked up.
- The file looks like this.

```yml
parameters:
  http.response.debug_cacheability_headers: true
services:
  cache.backend.null:
    class: Drupal\Core\Cache\NullBackendFactory
```

---
## Setting Up Drupal
- Add Twig debugging and auto reload.
```yml
parameters:
  twig.config:
    debug: true
    auto_reload: true
    cache: false
  http.response.debug_cacheability_headers: true
services:
  cache.backend.null:
    class: Drupal\Core\Cache\NullBackendFactory
```

---
## Setting Up Drupal
- Ensuring the setting has taken.

```
drush php:eval "var_export(\Drupal::getContainer()
   ->getParameter('twig.config'));"
```

---
## Setting Up Drupal
- Drupal will check the permissions of your settings.php files and ensure they are secure.
- To turn this off make sure this setting is enabled.
```php
$settings['skip_permissions_hardening'] = TRUE;
```

---
## Setting Up Drupal
- To turn off the permanent caches uncomment any line that looks like this.
```php
$settings['cache']['bins']['x'] = 'cache.backend.null';
```

---
# Devel

---
## Devel
- The Devel module is a good way of finding out more about the current state of Drupal.
- The Web Profiler is a sub module that can be used to drill into routes, database queries, hooks, cache systems and other things.

---
## Try it!
- Install Devel and Web Profiler.
- See it in action.

---
# Drupal Module Development

---
## What Is A Module?

- Adds a feature to a site.
- Can be turned on or off.
- Can define extra functionality or hook into and override other parts of Drupal.

---
## Types Of Module
- **Core** - Included in Drupal itself.
- **Contributed** - Any third party module you install. Referred to as "contrib".
- **Custom** - Any module you build yourself.

---
# Writing A Module

---

## The *.info.yml File
- Contains information about the module including what it does and what version on Drupal it is compatable with.
- In YAML format.
- The bare minimum required for a Drupal module to be picked up.

---
## mymodule.info.yml

```yml
name: 'My Module'
type: module
description: 'My amazing module.'
core_version_requirement: ^8 || ^9

```

---
# Hooks
The simplest building block of any module.

---
## What Is A Hook?
Hooks allow you to:
- Alter forms.
- Alter theme elements before rendering.
- React to events.
- Register plugins and templates.

Any module can define custom hooks.

---
## Some Popular Hooks

- `hook_form_alter($form, $fotm_state, $id)`
- `hook_theme($existing, $type, $theme, $path)`
- `hook_preprocess_page(&$variables)`
- `hook_theme_suggestions_alter(&$suggestions, $variables, $hook)`
- `hook_node_insert($entity)`
- `hook_node_update($entity)`
- `hook_update_9001(&$sandbox)`

---
## Naming Hooks
- Hooks are named after the module they appear in.

```php
hook_form_alter()
```
Becomes:
```php
mymodule_form_alter()
```

- The hook_form_alter() hook is called every time a form is created.

---
## Naming Hooks
- Some hooks also change their name based on context.

```php
hook_node_insert($entity)
```
Can also be:
```php
hook_user_insert($entity)
```

When detecting users being created.

---
## Example Hook

Use hook form alter to alter a form.

```php
use Drupal\Core\Form\FormStateInterface;

function mymodule_hook_form_alter(
  &$form,
  FormStateInterface $form_state,
  $form_id) {
  if ($form_id == 'node_article_form') {
    $form['title']['widget'][0]['value']['#default_value'] = t('title');
  }
}
```

---
## Try It!
- Create the file `mymodule.module`.
- Add a hook to alter a form.
- Flush caches!

---

# Translation

---
## Translation
- Why talk about multilingual code so early?
- It's baked into everything Drupal does. Drupal is multilingual from the start.
- You will see either t() or $this->t() a lot.
- These functions will register the translation with the Drupal translation system.

---
## t() Usage
- To use both t() and $this->t() just pass in a string.
```php
$translated = t('String');
```

```php
$translated = $this->t('String');
```
- Best practice is to pass it directly into where it is needed, rather than store in a variable.

---
<!-- _footer: "" -->
## Passing Arugments

Pass escaped output (should be your default choice).
```php
$t = t('Value = @value', ['@value' => '123']);
```
Wrap in &lt;em&gt; tags.
```php
$t = t('Value = %value', ['%value' => '123']);
```
Escape (used for URLs)
```php
$t = t('<a href=":url">@variable</a>',
  [':url' => $url, '@variable' => $variable]);
```

---
# Controllers

---
## Controllers

- Add an action for a particular **Route**.
- Parameters can be passed to the controller.
- Should return an array of content ready to be rendered or a response object.
- Multiple routes can use the same controller.

---
## Routes

- All controllers need a route.
- This tells Drupal what controller to use when a path is requested.
- Defined in a *.routes.yml file.

---
## Routes

Create a file at mymodule.routing.yml.

```yml
mymodule.controller_action:
  path: '/mycontroller/action'
  defaults:
    _controller: '\Drupal\mymodule\Controller\MyController::action'
    _title: 'My Controller'
  requirements:
    _access: 'TRUE'
```

---

## Controller

A basic controller looks like this.

```php
<?php

namespace Drupal\mymodule\Controller;

use Drupal\Core\Controller\ControllerBase;

class MyController extends ControllerBase {
  public function action() {
    // return a render array or a new response object.
  }
}

```

---
## Controller Return A Response

A basic controller looks like this.

```php
namespace Drupal\mymodule\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class MyController extends ControllerBase {
  public function action() {
    return new Response('Response.');
  }
}

```

---
## Different Types Of Response Objects Exist

- **Response** - Text based response.
- **HtmlResponse** - A HTML response.
- **JsonResponse** - JSON response.
- **XmlResponse** - XML response.
- **CacheableResponse** - A response that contains Drupal cache metadata.

---
## Try It!
- Create a route.
- Add a controller for the route.
- Return a response object.

**Hint**: Some cache clearing may be needed.

---
## Render Arrays

Render arrays are a hierarchical structure of elements that Drupal will convert into markup.

You can inject raw markup into render arrays, but it's generally best practice to use themes to render HTML.

---
<!-- _footer: "" -->
## Render Arrays

This render array:
```php
$build = [];
$build['description'] = [
  '#type' => 'html_tag',
  '#tag' => 'p',
  '#value' => $this->t('Some description.'),
];
return $build;
```
Will become:
```html
<p>Some description.</p>
```

---

## Render Arrays

This render array:

```php
$build = [];
$build['list'] = [
  '#theme' => 'item_list',
  '#items' => ['Item 1', 'Item 2'],
];
return $build;
```

Will become:

```html
<ul><li>Items 1</li><li>Item 2</li></ul>
```

---
## Try It!

- Change your controller to return a render array.

**Hint**: item_list, html_tag.

---
# Menu Links

---
## Menu Plugins

- You can inject menu items into Drupals menu system.
- Stored in the `*.links.menu.yml` file.
- These menu items are not editable.

```yml
mymodule.controller_action:
  title: 'MyModule Controller'
  description: 'A controller with an action.'
  route_name: mymodule.controller_action
  parent: system.admin
```
Menu link is created under /admin.

---
## Try it!

- Create a route.
- Create a controller to listen to that route.
- Return some content.
- Add a menu plugin to the controller.

---
## Passing Parameters To Routes

- This is known as adding a wildcard to a route.

```yml
mymodule.controller_action:
  path: '/mycontroller/action/{parameter}'
  defaults:
    _controller: '\Drupal\mymodule\Controller\MyController::action'
    _title: 'My Controller'
  requirements:
    _access: 'TRUE'
```

---
## Controller With Parameter

A basic controller looks like this.

```php
<?php

namespace Drupal\mymodule\Controller;

use Drupal\Core\Controller\ControllerBase;

class MyController extends ControllerBase {
  public function action($parameter) {
    // return a render array
  }
}

```

---
## Route Permissions

```yml
mymodule.controller_action:
  path: '/mycontroller/action/{parameter}'
  defaults:
    _controller: '\Drupal\mymodule\Controller\MyController::action'
    _title: 'My Controller'
  requirements:
    _permission: 'access content'
```

---
## Try It!

- Add a parameter to your route.
- Add a parameter to your controller.
- Make it do something interesting in your controller.

**Hint**: Use dynamic functions like `str_repeat()`, `rand()`, `date()`, `range()`.

---
# Forms

---
## Forms
- In Drupal, all forms are generated using the Form API.
- It's like a render array, but for form fields.
- By default, all forms use POST.

---
## Creating A Form

Change the route to point to a Form class.

```yml
mymodule.form:
  path: '/my-form'
  defaults:
    _form: '\Drupal\mymodule\Form\MyForm'
    _title: 'My Form'
  requirements:
    _access: 'TRUE'
```

---
## Creating A Form
- Add a class to the directory `src/Form/MyForm.php`.
- Bluebrint of a form class (<em>on next slide</em>).

---
```php
namespace Drupal\mymodule\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class MyForm extends FormBase {
  public function getFormId() {
    return 'mymodule-myform';
  }

  public function buildForm(array $form, 
   FormStateInterface $form_state
    ) {
    return $form;
  }

  public function submitForm(array &$form,   
   FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('Form submitted'));
  }
}
```

---
## Creating A Form
- The form API is very like the render array, but centered around form elements.
- The most common form elements are:
  - textfield
  - radios
  - checkbox
  - checkboxes
  - select
  - submit

---
## Creating A From
```php
$form['name'] = [
  '#type' => 'textfield',
  '#title' => $this->t('Name'),
  '#required' => TRUE,
];
$form['submit'] = [
  '#type' => 'submit',
  '#value' => $this->t('Submit'),
];
```

---
## Form Submission

```php
public function submitForm(array &$form,   
 FormStateInterface $form_state) {
  $name = $form_state->getValue('name');

  $this->messenger()->addStatus(
    $this->t('Submitted the name %name', ['%name' => $name]));
}
```

---
## Form Validation
- Form validation happens in the validateForm() method (if implemented).
- If any errors are triggered then the submit handler is not called.
- Note that if you set the field to be "#required" then it will automatically get validated.

---
## Form Validation
```php
public function validateForm(array &$form,
 FormStateInterface $form_state) {
  $name = $form_state->getValue('name');

  if ($name == 'Bob') {
    // Name is Bob, trigger error!
    $form_state->setErrorByName('name', $this->t('Name is Bob. Cannot continue.'));
  }
}
```

---
## Try it!
- Create a route for a form.
- Create a form.
- Submit the form.

---
# Services And Dependency Injection

---
# Resources

- [#! code - Drupal 9: An Introduction To Services And Dependency Injection](https://www.hashbangcode.com/article/drupal-9-introduction-services-and-dependency-injection)

---
# Plugins

---
## Plugins

- Provide functionality through a common interface.
- Most things in Drupal are actually plugins.
- Entity types, fields, blocks, image formats, routes are all plugins.
- You can also define custom plugins.

---
# Entities

---
## Entities
- Entities in Drupal represent "things".
- Nodes, users, comments, taxonomy terms are all entities.

---
## Entities - Bundles
- Entites can have sub-types, called bundles.
- Bundles inherit all of the functionality of the entity.
- Think of them as extended classes.

---
## Entities - Bundles
| Entity       | Bundles              |
|--------------|----------------------|
| Node         | Articles, Basic Page |
| Media        | Image, Video         |
| Vocabulary   | Category, Tags       |

---
## Loading Entites

By ID:

```php
$entity_id = 123;
$entity = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->load($entity_id);
```
---

## Loading Entities
By field value:

```php
$value = 'some value';
$entity = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->loadByProperties(['field_name' => $value]);
```

---
## Loading Field Values

```php
$field_value = $entity->get('field_name')->getValue()[0]['value'];
```

---
## Creating Entities

Create a node.

```php
    $node = Node::create([
      'title' => 'Article title',
      'type' => 'article',
    ]);

    $node->save();

    $newArticleId = $node->id();
```

---
# Drupal Cache

---
## Drupal Cache
- Drupal has a robust and dynamic cache system.
- Can be used as a static cache bin or as a dynamic cache.
- It's important to understand what the components are.
- Ideally, you want to cache as much as possible in the page.
- For anonymous users you typically want the entire page cached.

---
<!-- _footer: "" -->
## Cache Meta Data
- Added to render arrays to inform Drupal about how to cache the data.

Cache for an hour.
```php
'#cache' => [
  'max-age' => 3600,
]
```
Cache for ever.
```php
'#cache' => [
  'max-age' => \Drupal\Core\Cache\Cache::PERMANENT,
]
```

---
## Cache Tags
- Cached data can be cached to show that it refernces something.
- This means that when upstream caches are cleared the tagged caches can also be cleared.
- For example, a page of content is saved. The cache of that page can be flushed from cached pages, views or anywhere else it is used.

---
<!-- _footer: "" -->

## Cache Tags
Create a cache tag for node 1 and node 2.
```php
'#cache' => [
  'tags' => ['node:1', 'node:2'],
]
```

Create a cache tag for current user.
```php
$cacheTags = User:load(\Drupal::currentUser()->id())->getCacheTags();
...
'#cache' => [
  'tags' => $cacheTags,
]
```

---
## Cache Contexts
- This tells Drupal how to the data should be cached on the site.
- For example, the context "user.roles" will store the cache for each user role.

```php
'#cache' => [
  'contexts' => ['user.roles', 'url.path_is_front'],
]

```
---
## Cache Contexts
- Cache Contexts are hierarchical, so Drupal will cache the most granular variation to avoid unnecessary variations.
- For example, when caching a page per user its pointless to also cache a block on that page per user role.

---
## Cache Methods
- Some plugins extend the CacheableDependencyInterface interface.
- This gives them access to the methods getCacheContexts(), getCacheTags(), and getCacheMaxAge(). 

---
<!-- _footer: "" -->
```php
public function getCacheTags() {
  // With this when your node change your block will rebuild.
  if ($node = \Drupal::routeMatch()->getParameter('node')) {
    // If there is node add its cachetag.
    $tags = ['node:' . $node->id()]
    return Cache::mergeTags(parent::getCacheTags(), $tags);
  }
  // Return default tags instead.
  return parent::getCacheTags();
}

public function getCacheMaxAge() {
  return Cache::PERMANENT;
}

public function getCacheContexts() {
  return ['url'];
}
```

---
## Cache API
- Get and set things from the Drupal cache.
- Integrates with cache tags if needed.

Get from cache.
```php
\Drupal::cache()->get('cache_id');
```
Set data to cache. 
```php
\Drupal::cache()->set('cache_id', $data, $max_age, $cache_tags);
```

---
<!-- _footer: "" -->
## Cache API
```php
use Drupal\Core\Cache\Cache;

$uid = \Drupal::currentUser()->id(); 
$cache_id = 'course:' . $uid;

if ($data = \Drupal::cache()->get($cache_id)) {
  return $item;
}

$data = massive_calculation();
$cache_tags[] = 'uid:' . $uid;

\Drupal::cache()->set($cache_id, $data, Cache::PERMANENT, $cache_tags);

return $item;
```
---
## Cache
- Some things (e.g. blocks) have special callback to return cache tags and cache context information.
- The methods getCacheTags() getCacheContexts() must return an array informing Drupal of the tags and contexts.


---
# Templates

---
## Tempaltes
- Tell Drupal about custom templates you want to use.
- Defined with a hook_theme() hook in modules or themes.

---
## Templates
- Custom templates can be deinfed using hook_theme().

```php
function my_module_theme() {
  return [
    'my_custom_tempalte' => [
      'variables' => [
        'description' => '',
        'some_list' => [],
      ],
    ],
  ];
}
```

---
## Template

- The new hook can be used just like any other theme.

```php
$build = [];
$build['content'] = [
  '#theme' => 'my_custom_tempalte',
  '#description' => $this->t('A description.'),
  '#some_list' => ['item1', 'item2'],
];
```

---
<!-- _footer: "" -->
## Template

- The custom theme needs a custom tempalte.
- The <em>templates</em> directory is the default location for templates in a module.
- Our hook will use <em>templates/my_custom_tempalte.html.twig</em>.

```twig
<p>{{ description }}</p>

{% for list_item in some_list %}
  {{ list_item }}
{% endfor %}

```

---
## Try it!
- Create a hook_theme().
- Create a twig file.
- Render it in a normal render array.

**Hint**: Some cache clearing may be needed.

---
# CSS & JavaScript

---
## Asset Libraries
- CSS and JavaScript are loaded using asset libraries.
- Defined in a *.libraries.yml file.
- A library can contain both CSS and JavaScript files.
- Can collect together functionality.
- Dependencies can be used to ensure libraries are loaded together.

---
## Define A Library
- A library file in a module.

```yml
some_library:
  version: 1.x
  css:
    layout:
      css/some-library-layout.css: {}
    theme:
      css/some-library-theme.css: {}
  js:
    js/some-library.js: {}
  dependencies:
    - core/jquery
```

---
## CSS Style Types
- There are 5 types of CSS types which control how the order in which the CSS files are loaded.

base
layout
component
state
theme

---
## Libraries Attachment
- hook_page_attachments() can attach any library to any page.

```php
function mymodule_page_attachments(array &$attachments) {
  $attachments['#attached']['library'][] = 'mympdule/some_library';
  }
}
```

---
## Try it!
- Create CSS code to change the background colour of the site.
- Create a library.
- Inject CSS into the site.

---
## Libraries Attachment
- Attach the library to any render array.
- For example, in a controller:

```php
public function action() {
  $build = [];

  $build['#attached']['library'][] = 'mympdule/some_library';

  return $build;
}
```

---
## Try it!
- Inject the library into a controller.
- Make sure the library appears at the bottom of the page.

**Hint**: The _footer_ setting will come in handy here.

---
# Custom Blocks

---
## Custom Blocks
- Add a class to src\Plugin\Block.
- Needs a @Block annotation.
- Extends Drupal\Core\Block\BlockBase.
- The build() method returns content as a render array.

---
## Custom Block

```php
namespace Drupal\mymodule\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a custom block.
 *
 * @Block(
 *  id = "mymodule_custom_block",
 *  label = "MyModule Custom Block",
 *  admin_label = @Translation("MyModule Custom Block"),
 * )
 */
class ArticleHeaderBlock extends BlockBase {
  public function build() {}
}

```

---
## Custom Block
- Implement ContainerFactoryPluginInterface to use services.
- You can then use the create()/__construct() mechanism to pull in the services needed.

---

```php
namespace Drupal\mymodule\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Article Header' block.
 *
 * @Block(
 *  id = "hashbangcode_article_header",
 *  label = "Article Header",
 *  admin_label = @Translation("Article Header"),
 * )
 */
class ArticleHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {
}

```

---
## Try it!
- Create a block.
- Output some content.
- Place the block on your site.

---
<!-- _footer: "" -->
## Configure Blocks

- The blockForm()/blockSubmit() allows configuration options to be saved to the block.

```php
public function blockForm($form, FormStateInterface $form_state) {
  $form['setting'] = [
    '#type' => 'textfield',    
    '#default_value' => $this->configuration['setting'],
  ];
  return $form;
}

public function blockSubmit($form, FormStateInterface $form_state) {
  $this->configuration['setting'] = $form_state->getValue('setting');
}
```

---
## Try it!
- Add a configuration form to your block.
- Pull out the configuration value into the block content.

---
<!-- _footer: "" -->
## Block Caches
- The methods getCacheTags() getCacheContexts() must return an array informing Drupal of the tags and contexts.

```php
public function getCacheTags() {
  $node = \Drupal::routeMatch()->getParameter('node');
  return Cache::mergeTags(parent::getCacheTags(), ['node:' . $node->id()]);
}
```

```php
public function getCacheContexts() {
  return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
}
```

---
## Design Philosophy
- Think about modules in the most generic way possible. Even when naming it.
- Use contfiguration to control what your module acts upon.
- You should be thinking "this might make a good contrib module".

---
## Coding Standards
- Drupal has a number of coding standards covering PHP, JavaScript, YAML and CSS.
- Following them will make your module better, more secure, more maintainable and usable by third parties.
