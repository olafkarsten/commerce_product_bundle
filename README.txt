Commerce Product Bundle
-----------------------

This module provides for Drupal Commerce a product bundling functionality.

You can define products that consists of other  products. The customer can 
select from different  sub products. This is similar to the core attribute 
functionality with the difference that the customer can select  multiple 
products in on one page.

The bundle  is then  represented by  one order line item.  This allows the
maximum  compatibility  with  other  modules  such as  shipping or payment 
modules. 

The module is in alpha stage and under development. So do not expect a production
ready version. At the moment use it for testing purposes only. If you find any
bugs - and you will, please file an issue.
http://drupal.org/project/issues/commerce_product_bundle


Installation
------------
Install the  module  as  usual.  

1) Setup a product type
Learn more about product types, products and product display in drupal commerce
http://www.drupalcommerce.org/node/289

2) Add at least two Product Reference Fields to the product type. 

3) Go to the Administer Display Tab in the product type admin form.
http://www.example.com/admin/commerce/products/types/YOUR-PRODUCT-TYPE/display
Set the display formatter at all product reference fields to 
'product bundle: add to cart form'.

4) Add a product with the new product type, and choose your referenced products.

5) Go and create an new product display content type, if you do not have one.
http://www.drupalcommerce.org/node/293
Add a product reference field, that can reference Products from your 
in Step 1) created Product Type.

6) Create a product display node and add a reference to the product you just
created in Step 4)

You're done.

Sponsored by www.customweb.ch
