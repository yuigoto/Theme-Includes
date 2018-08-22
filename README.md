# Theme Includes

> Tentativa de fazer um fork documentado, em português, do repositório Theme Includes, criado pelo pessoal do ThemeFuse (https://github.com/ThemeFuse/Theme-Includes).

Uma forma de organizar arquivos para o seu tema.

## Estrutura de Pastas

```text
[seu-tema]/
└─inc/
  ├─static.php      # wp_enqueue_style() e wp_enqueue_script()
  ├─menus.php       # register_nav_menus()
  ├─hooks.php       # add_filter() e add_action()
  ├─helpers.php     # Funções de ajuda e outras classes
  ├─widgets/        # Widgets do tema
  │ ├─{nome-widget}/
  │ │ ├─class-widget-{nome-widget}.php # class Widget_{Nome_Widget} extends WP_Widget { ... }
  │ │ ├─algum-arquivo.php
  │ │ └─alguma-pasta/
  │ │   └─...
  │ └─...
  └─includes/       # Todos os arquivos php são solicitados automaticamente
    ├─algum-arquivo.php
    └─...
```

## Descrição dos Arquivos

* `helpers.php`, `hooks.php`, `includes/*.php` são incluídos logo de cara
* `static.php` é incluso pela action [`wp_enqueue_scripts`](http://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts)
* `widgets/{hello-world}/class-widget-{Hello_World}.php` são incluídos na action `widgets_init`

## Instalação

1. [Faça o Download](https://github.com/yuigoto/Theme-Includes/archive/master.zip) do arquivo
2. Extraia o conteúdo para a raíz do seu tema
3. Incluia o arquivo `init.php` no `functions.php` do seu tema

	```php
	include_once get_template_directory() .'/inc/init.php';
	```
