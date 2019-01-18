<?php

namespace app\models\repositories;


use app\models\Cart;

class CartRepository extends Repository
{
    /**
     * Returns 'cart' - the name of a cart table.
     * @return string
     */
    public function getTableName(): string
    {
        return 'cart';
    }

    /**
     * Returns the Cart class name.
     * @return string
     */
    public function getEntityClass(): string
    {
        return Cart::class;
    }

    /**
     * Returns an array of cart products objects.
     * @return array
     */
    public function getCart()
    {
        $sql = "SELECT products.image_path, products.name, cart.amount, (products.price * cart.amount) AS price FROM  
                products, cart WHERE cart.product_id = products.id";

        return $this->db->queryAllObjects($sql, \stdClass::class);
    }

    /**
     * Adds a new product to the cart (in DB).
     */
    public function addToCart()
    {
        $id = $_GET['id'];
        $product = (new ProductRepository())->getOne($id);
        $cart_products = $this->getAll();

        foreach ($cart_products as $cart_product) {
            if ($cart_product->product_id === $product->id) {
                $cart_product = $this->getOne($product->id);
                $cart_product->amount++;
                $this->commitChange($cart_product);
                header('Location: /');
                return;
            }
        }

        $cart_product = new Cart();
        $cart_product->product_id = $product->id;
        $cart_product->amount = 1;
        $this->commitChange($cart_product);
        header('Location: /');
    }
}