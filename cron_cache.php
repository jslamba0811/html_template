<?php
/**
* cron_refresh_cache.php
* 
* @copyright  copyright (c) 2009 toniyecla[at]gmail.com
* @license    http://opensource.org/licenses/osl-3.0.php open software license (OSL 3.0)
*/

( !$_SERVER["HTTP_USER_AGENT"] ) or die ( "Nothing to do\n" ); // to run via local browser use ($_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"]) 

require_once 'app/Mage.php';
umask( 0 );
Mage::app( "default" ); // if getting error change this line to Mage::app(Mage::app()->getStore());  
$ver = Mage::getVersion();
$userModel = Mage::getModel( 'admin/user' );
$userModel -> setUserId( 0 );
Mage::getSingleton( 'admin/session' )->setUser( $userModel );

echo "Refreshing cache...\n";
Mage::app()->cleanCache();
$enable = array();
foreach ( Mage::helper( 'core' )->getCacheTypes() as $type => $label ) {
    $enable[$type] = 1;
    } 

Mage::app()->saveUseCache( $enable ); 
refresh_cache(); // call refresh function 

function refresh_cache() 
    {    
        $this -> notify( 'Refreshing cache' );
        try {
            Mage :: getSingleton( 'catalog/url' ) -> refreshRewrites();
            $this -> notify( 'Catalog Rewrites was refreshed successfully', 'blank');
            } 
        catch ( Exception $e ) {
            $this -> notify( $e -> getMessage(), 'warning' );
            }
        try {
            Mage :: getSingleton( 'catalog/index' ) -> rebuild();
            $this -> notify( 'Catalog Index was rebuilt successfully', 'blank');
            } 
        catch ( Exception $e ) {
            $this -> notify( $e -> getMessage(), 'warning' );
            }
        try {
            $flag = Mage :: getModel( 'catalogindex/catalog_index_flag' ) -> loadSelf();
            if ( $flag -> getState() == Mage_CatalogIndex_Model_Catalog_Index_Flag :: STATE_RUNNING ) {
                $kill = Mage :: getModel( 'catalogindex/catalog_index_kill_flag' ) -> loadSelf();
                $kill -> setFlagData( $flag -> getFlagData() ) -> save();
                } 
            $flag -> setState( Mage_CatalogIndex_Model_Catalog_Index_Flag :: STATE_QUEUED ) -> save();
            Mage :: getSingleton( 'catalogindex/indexer' ) -> plainReindex();
            $this -> notify( 'Layered Navigation Indices was refreshed successfully', 'blank');
            } 
        catch ( Exception $e ) {
            $this -> notify( $e -> getMessage(), 'warning' );
            }
        try {
            Mage :: getModel( 'catalog/product_image' ) -> clearCache();
            $this -> notify( 'Image cache was cleared successfully', 'blank');
            } 
        catch ( Exception $e ) {
            $this -> notify( $e -> getMessage(), 'warning' );
            }
        try {
            Mage :: getSingleton( 'catalogsearch/fulltext' ) -> rebuildIndex();
            $this -> notify( 'Search Index was rebuilded successfully', 'blank');
            } 
        catch ( Exception $e ) {
            $this -> notify( $e -> getMessage(), 'warning' );
            }
        try {
            Mage :: getSingleton( 'cataloginventory/stock_status' ) -> rebuild();
            $this -> notify( 'CatalogInventory Stock Status was rebuilded successfully', 'blank');
            } 
        catch ( Exception $e ) {
            $this -> notify( $e -> getMessage(), 'warning' );
            }
        try {
            Mage :: getResourceModel( 'catalog/category_flat' ) -> rebuild();
            $this -> notify( 'Flat Catalog Category was rebuilt successfully', 'blank');
            } 
        catch ( Exception $e ) {
            $this -> notify( $e -> getMessage(), 'warning' );
            }
        try {
            Mage :: getResourceModel( 'catalog/product_flat_indexer' ) -> rebuild();
            $this -> notify( 'Flat Catalog Product was rebuilt successfully', 'blank');
            } 
        catch ( Exception $e ) {
            $this -> notify( $e -> getMessage(), 'warning' );
            }
        }  


?>