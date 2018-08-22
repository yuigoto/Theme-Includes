<?php if ( ! defined( 'ABSPATH' ) ) die ( 'Acesso direto ao arquivo negado.' );

/**
 * Theme_Includes
 * ----------------------------------------------------------------------
 * Responsável pela inclusão de todos os arquivos com scripts, menus, widgets
 * e outras customizações do tema.
 *
 * @author      Fabio Y. Goto <lab@yuiti.com.br>
 * @since       0.0.1
 */
class Theme_Includes {
    /**
     * Caminho relativo da pasta na qual se encontra este arquivo de classe.
     *
     * @var string|null
     */
    private static $rel_path = null;
    
    /**
     * Armazena uma função anônima/callable, para includes isolados de arquivos.
     *
     * Originalmente consistia em um `create_function`. Modificado para função
     * anônima, visto que `create_function` foi descontinuado.
     *
     * @var callable
     */
    private static $include_isolated_callable;
    
    /**
     * Status de inicialização da classe de includes.
     *
     * @var bool
     */
    private static $initialized = false;
    
    /**
     * Construtor, inicializa includes.
     */
    public static function init()
    {
        // Se estiver inicializado, retorna, ou então define $intiialized
        if ( self::$initialized ) {
            return;
        } else {
            self::$initialized = true;
        }
        
        /**
         * Inclui um arquivo isoladoamente, para que ele não tenha acesso
         * às variáveis do contexto atual.
         *
         * @param string $path
         *      Caminho de arquivo a ser incluido
         */
        self::$include_isolated_callable = function( string $path ) {
            include $path;
        };
    
        /**
         * Executa tanto no frontend, quanto no backend.
         */
        {
            self::include_child_first( '/helpers.php' );
            self::include_child_first( '/hooks.php' );
            self::include_all_child_first( '/includes' );
            
            // Adiciona actions
            add_action( 'init', array( __CLASS__, '_action_init' ) );
            add_action(
                'widgets_init',
                array( __CLASS__, '_action_widgets_init' )
            );
        }
    
        /***
         * Executa apenas no frontend.
         */
        if ( ! is_admin() ) {
            add_action(
                'wp_enqueue_scripts',
                array( __CLASS__, '_action_enqueue_scripts' ),
                20
                /**
                 * Realiza um include mais tardio, para que seja possível
                 * executar `wp_dequeue_style|script()`.
                 */
            );
        }
    }
    
    /**
     * Retorna o caminho relativo deste arquivo de classe.
     *
     * O valor de `$append` é opcional, e deve indicar uma pasta ou nome
     * de arquivo a ser adicionado como segmento final no caminho.
     *
     * @param string $append
     *      Segmento a ser adicionado ao caminho relativo desta classe
     * @return string
     */
    private static function get_rel_path( string $append = '' ): string
    {
        // Se nulo, define raíz do caminho relativo.
        if (self::$rel_path === null) {
            self::$rel_path = '/' . basename( dirname( __FILE__ ) );
        }
        
        // Retorna caminho + segmento adicional
        return self::$rel_path . $append;
    }
    
    
    /**
     * Inclui arquivos necessários ao tema, usando o callable definido no
     * construtor.
     *
     * Primeiro inclui os caminhos no tema filho (se for um), depois os arquivos
     * do template principal.
     *
     * @param string $dir_rel_path
     *      Caminho relativo desejado para busca
     */
    private static function include_all_child_first( string $dir_rel_path )
    {
        $paths = array();
        
        if ( is_child_theme() ) {
            $paths[] = self::get_child_path( $dir_rel_path );
        }
        
        $paths[] = self::get_parent_path( $dir_rel_path );
        
        // Inclui tudo isoladamente
        foreach ( $paths as $path ) {
            if ( $files = glob( $path . '/*.php' ) ) {
                foreach ( $files as $file ) {
                    self::include_isolated( $file );
                }
            }
        }
    }
    
    /**
     * Converte o nome de um diretório/estrutura em um nome de classe.
     *
     * Ex.: `module-test-example` => `Module_Test_Example`.
     *
     * @param string $dirname 'foo-bar'
     *      Caminho a ser convertido
     * @return string 'Foo_Bar'
     */
    private static function dirname_to_classname( string $dirname ): string
    {
        $class_name = explode( '-', $dirname );
        $class_name = array_map( 'ucfirst', $class_name );
        $class_name = implode( '_', $class_name );
        
        return $class_name;
    }
    
    /**
     * Retorna o caminho para `$rel_path` dentro do tema principal.
     *
     * @param string $rel_path
     *      Caminho relativo deste arquivo
     * @return string
     */
    public static function get_parent_path( string $rel_path ): string
    {
        return get_template_directory() . self::get_rel_path( $rel_path );
    }
    
    /**
     * Retorna o caminho para `$rel_path` dentro do tema filho.
     *
     * @param string $rel_path
     *      Caminho relativo desejado dento da pasta do tema filho.
     * @return string
     */
    public static function get_child_path( string $rel_path ): string
    {
        // Não é um template filho?
        if ( ! is_child_theme() ) return null;
        
        return get_stylesheet_directory() . self::get_rel_path( $rel_path );
    }
    
    /**
     * Executa o callable (`include_isolated_callable`), para inclusão de
     * arquivos isoladamente do contexto.
     *
     * @param string $path
     *      Caminho para inclusão
     */
    public static function include_isolated( string $path )
    {
        call_user_func( self::$include_isolated_callable, $path );
    }
    
    /**
     * Inclui o conteúdo de `$rel_path` primeiro no tema filho, depois no
     * tema pai.
     *
     * @param string $rel_path
     *      Caminho relativo para buscar dentro das pastas de tema
     */
    public static function include_child_first( string $rel_path )
    {
        // É tema filho? Inclui se o arquivo existir
        if ( is_child_theme() ) {
            $path = self::get_child_path( $rel_path );
            
            if ( file_exists( $path ) ) self::include_isolated( $path );
        }
        
        // Então inclui o arquivo do tema pai
        {
            $path = self::get_parent_path( $rel_path );
            
            if ( file_exists( $path ) ) self::include_isolated( $path );
        }
    }
    
    /**
     * Inclui o conteúdo de `$rel_path` primeiro no tema pai, depois no
     * tema filho.
     *
     * @param string $rel_path
     *      Caminho relativo para buscar dentro das pastas de tema
     */
    public static function include_parent_first( string $rel_path )
    {
        // Inclui o arquivo do tema pai
        {
            $path = self::get_parent_path( $rel_path );
            
            if ( file_exists( $path ) ) self::include_isolated( $path );
        }
        
        // Se tema filho, inclui se o arquivo existir
        if ( is_child_theme() ) {
            $path = self::get_child_path( $rel_path );
            
            if ( file_exists( $path ) ) self::include_isolated( $path );
        }
    }
    
    /**
     * Inclui os scripts de `static.php` dos temas.
     *
     * Prioriza os arquivos do tema pai na ordem de carregamento.
     *
     * @internal
     */
    public static function _action_enqueue_scripts()
    {
        self::include_parent_first( '/static.php' );
    }
    
    /**
     * Inclui os scripts de `menus.php` dos temas.
     *
     * Prioriza os arquivos do tema filho na ordem de carregamento.
     *
     * @internal
     */
    public static function _action_init()
    {
        self::include_child_first( '/menus.php' );
    }
    
    /**
     * Inicializa os scripts de widgets do tema.
     *
     * Prioriza os arquivos do tema filho na ordem de carregamento.
     *
     * @internal
     */
    public static function _action_widgets_init()
    {
        {
            $paths = array();
            
            if ( is_child_theme() ) {
                $paths[] = self::get_child_path( '/widgets' );
            }
            
            $paths[] = self::get_parent_path( '/widgets' );
        }
        
        $included_widgets = array();
        
        // Busca nos caminhos
        foreach ( $paths as $path ) {
            // Busca apenas diretórios
            $dirs = glob( $path . '/*', GLOB_ONLYDIR );
            
            if ( ! $dirs ) continue;
            
            foreach ( $dirs as $dir ) {
                $dirname = basename( $dir );
                
                if ( isset( $included_widgets[ $dirname ] ) ) {
                    /**
                     * Isso acontece quando um widget no tema filho deseja
                     * sobrescrever o widget no tema pai.
                     *
                     * No caso, o widget já foi incluso e não há necessidade de
                     * fazê-lo novamente.
                     */
                    continue;
                } else {
                    $included_widgets[ $dirname ] = true;
                }
                
                // Define caminho final para inclusão
                self::include_isolated( "{$dir}/class-widget-{$dirname}.php" );
                
                // Registra widget
                register_widget(
                    'Widget_' . self::dirname_to_classname( $dirname )
                );
            }
        }
    }
}

// Inicializa includes
Theme_Includes::init();
