<?php

namespace app\models\repositories;


use app\models\entities\Cart;


/**
 * Class CartRepository contains methods working with the database cart table.
 * @package app\models\repositories
 */
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
     * Returns an array of cart products objects by user id.
     * @param int $user_id
     * @return array
     */
    public function getCart(int $user_id)
    {
        $sql = "SELECT cart.user_id, cart.product_id, products.image_path, products.name, cart.amount, (products.price * cart.amount) AS price FROM  
                products, cart WHERE cart.product_id = products.id AND cart.user_id = {$user_id}";

        return $this->db->queryAllObjects($sql, \stdClass::class);
    }


    /**
     * Adds a new product to the cart (in DB).
     * @param int $user_id
     * @param int $id
     * @param int $amount
     */
    public function addToCart(int $user_id, int $id, int $amount)
    {
        $cart_products = $this->getAll();

        $product = new Cart();
        $product->user_id = $user_id;
        $product->product_id = $id;
        $product->amount = $amount;

        foreach ($cart_products as $cart_product) {
            if ($cart_product->product_id == $id && $cart_product->user_id == $user_id) {
                $cart_product = $this->getOneByMany(['user_id' => $user_id, 'product_id' => $id]);
                $product->id = $cart_product->id;
                $product->amount = $cart_product->amount + $amount;
                break;
            }
        }
        $this->commitChange($product);
    }


    /**
     * Removes an item from a cart by reducing amount column.
     * @param int $user_id
     * @param int $id
     */
    public function removeOne(int $user_id, int $id)
    {
        $cart_product = $this->getOneByMany(['user_id' => $user_id, 'product_id' => $id]);

        if ((int)$cart_product->amount > 1) {
            $product = new Cart();
            $product->user_id = $user_id;
            $product->product_id = $id;
            $product->id = $cart_product->id;
            $product->amount = $cart_product->amount - 1;
            $this->commitChange($product);
        } else {
            $this->remove($user_id, $id);
        }
    }

    /**
     * Removes a whole record from a 'cart' table.
     * @param int $user_id
     * @param int $id
     */
    public function remove(int $user_id, int $id)
    {
        $product = new Cart();
        $product->user_id = $user_id;
        $product->product_id = $id;
        $cart_product = $this->getOneByMany(['user_id' => $user_id, 'product_id' => $id]);
        $product->id = $cart_product->id;

        $this->delete($product);
    }

    /**
     * Removes all the records from a 'cart' table.
     * @param int $user_id
     */
    public function clearCart(int $user_id)
    {
        $cart_products = $this->getAll();

        foreach ($cart_products as $product) {
            if ($product->user_id == $user_id){
                $this->delete($product);
            }
        }
    }
}