<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

//include all your model files here
require 'Model/User.php';
require 'Model/UserMaker.php';
require 'Model/Product.php';
require 'Model/ProductMaker.php';
require 'Model/Groups.php';
require 'Model/GroupMaker.php';

class HomepageController
{
    public function render()
    {
        //make array of user objects
        $makeUser = new UserMaker();
        $makeUser->fetchUsers();
        $userArray = $makeUser->makeUserArray();
        //initiate users
        if (!isset($_POST['users'])) {
            $userId = 1;
        } else {
            $userId = (int)$_POST['users'];
        }
        //make array of product objects
        $makeProduct = new ProductMaker();
        $makeProduct->fetchProducts();
        $productArray = $makeProduct->makeProductArray();

        //initiate products
        if (!isset($_POST['product'])) {
            $productId = 1;
        } else {
            $productId = $_POST['product'];
        }

        //make array of group objects
        $makeGroups = new GroupMaker();
        $makeGroups->fetchGroups();
        $groupArray = $makeGroups->makeGroupArray();


        //array for all the groups a user belongs to
        $allUserGroups = $makeGroups->makeUserGroupArray($userArray, $userId);



///////////////---------------THIS SHOULD NOT BE  IN RENDER---------------------------
        //array for all fixed numbers
        $fixedArray = [];
        //array for all variable numbers
        $variableArray = [];

        //forloop to fill the arrays
        for ($i = 0; $i < count($allUserGroups); $i++) {
            if ($allUserGroups[$i]->getFixedDiscount() != 0) {
                array_push($fixedArray, $allUserGroups[$i]->getFixedDiscount());
            }
            if ($allUserGroups[$i]->getVariableDiscount() != 0) {
                array_push($variableArray, $allUserGroups[$i]->getVariableDiscount());
            }

        }
        //gets the highest variable
        if (!empty($variableArray)) {
            $maxVariable = max($variableArray);
        } else {
            $maxVariable = 0;
        }

        //counts all the fixed numbers
        function countFixed($v1, $v2)
        {
            return $v1 + $v2;
        }

        //counts all the fixed numbers
        if (!empty($fixedArray)) {
            $countFixed = array_reduce($fixedArray, "countFixed");
        } else {
            $countFixed = 0;
        }
        //the price minus only the fixed reduction
        $fixedreductionResult = round($productArray[$productId]->getProductPrice() - $countFixed, 2);
        if ($fixedreductionResult < 0) {
            $fixedreductionResult = 0;
        }

        $variablePercentage = $maxVariable / 100;
        $reductionVariable = $variablePercentage * $productArray[$productId]->getProductPrice();
        //the price with only the variable reduction
        $variableReductionResult = round($productArray[$productId]->getProductPrice() - $reductionVariable, 2);

        $compareArray = [];
        array_push($compareArray, $fixedreductionResult, $variableReductionResult);
        $winningDiscount = min($compareArray);

        //see what discount is better
        if ($winningDiscount == $fixedreductionResult) {
            $lowestDiscount = "fixed";
        } elseif ($winningDiscount == $variableReductionResult) {
            $lowestDiscount = "variable";
        }

        $finalResultMinFixed = $productArray[$productId]->getProductPrice() - $countFixed;
        $finalResultPercentage = $finalResultMinFixed * $variablePercentage;

        //the final result
        $finalResult = round($finalResultMinFixed - $finalResultPercentage, 2);

        if ($finalResult < 0) {
            $finalResult = 0;
        }

        if (!isset($_POST['quantity'])) {
            $quantity = 0;
        } else {
            $quantity = $_POST['quantity'];
        }


        if ($quantity >= 20 ) {
            $finalTotalResult = ($quantity * $finalResult) * 0.9;
        } elseif  ($quantity >= 40) {
            $finalTotalResult = ($quantity * $finalResult) * 0.8;
        } else {
            $finalTotalResult = $quantity * $finalResult;
        }




        require 'View/homepage.php';
    }
}