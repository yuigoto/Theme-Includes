<?php if ( ! defined( 'ABSPATH' ) ) die ( 'Acesso direto ao arquivo negado.' );

/**
 * Class Widget_Demo_Test
 * ----------------------------------------------------------------------
 * Widget de teste para include automático.
 *
 * @author      Fabio Y. Goto <lab@yuiti.com.br>
 * @since       0.0.1
 */
class Widget_Demo_Test extends WP_Widget
{
    /**
     * Widget_Demo_Test constructor.
     */
    public function __construct()
    {
        parent::__construct( false, 'Demo Test', array( 'description' => '' ) );
    }
    
    /**
     * {@inheritdoc}
     */
    public function widget( $args, $instance )
    {
        echo '<p>Demo Test Widget</p>';
    }
}
