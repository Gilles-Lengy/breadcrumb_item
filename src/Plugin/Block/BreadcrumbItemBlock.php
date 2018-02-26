<?php

namespace Drupal\breadcrumb_item\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;

/**
 * Provides a custom block breadcrumb item.
 *
 * @Block(
 *   id = "breadcrumb_item_block",
 *   admin_label = @Translation("Breadcrumb Item"),
 *
 * )
 */
class BreadcrumbItemBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        $monTitre = "";
        $request_uri = $_SERVER["REQUEST_URI"];

        $path_elements = explode('/', $request_uri);

        // // View // Content
        $route_name = \Drupal::routeMatch()->getRouteName();
        $route_parameters = \Drupal::routeMatch()->getRawParameters()->all();
        $menu_link_service = \Drupal::getContainer()->get('plugin.manager.menu.link');
        $menu_tree = \Drupal::menuTree();
        // MENU FANTOME
        $menu_name_fantome = 'menu-fantome';
        $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name_fantome);
        $parameters->setMinDepth(0);
        //Delete comments to have only enabled links
        $parameters->onlyEnabledLinks();


        $tree_fantome = $menu_tree->load($menu_name_fantome, $parameters);
        $manipulators = array(
            array('callable' => 'menu.default_tree_manipulators:checkAccess'),
            array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
        );
        $tree_fantome = $menu_tree->transform($tree_fantome, $manipulators);
        foreach ($tree_fantome as $item) {
            $titre = $item->link->getTitle();
            $item_route_name = $item->link->getRouteName(); // View
            $meta_data = $item->link->getMetaData(); // Content
            $result_intersect_path_elements_meta_data = array_intersect($path_elements, $meta_data);
            if ($monTitre == "" && (($item_route_name == $route_name || empty($result_intersect_path_elements_meta_data) == false) || ($meta_data["view_id"] == "actualites_spip"))) {// $meta_data["view_id"] suite import Franck Ruzzin, l'id de la view à changé...
                $monTitre = $titre;
            }
        }

        // // Node only
        if ($monTitre == "" && $node = \Drupal::routeMatch()->getParameter('node')) {
            $monTitre = $node->getTitle();
        }

        return array(
            '#title' => $monTitre,
            '#cache' => array(
                'contexts' => array('url.path'),
            )
        );
    }

}
