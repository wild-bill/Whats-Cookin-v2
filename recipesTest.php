<?php

# 5.27.18
# to debug the current directory. on mamp it's always htdocs, but when I move to 
# dreamhost server it may be helpful?
#echo __DIR__;

# we need to require the autoload function (it autoloads Composer) in order to use Unirest.
# we need to use Unirest to use the Mshape API. work properly.
require '/home/wilkry1/vendor/autoload.php';


/*

See spoonacular API here: https://market.mashape.com/spoonacular/recipe-food-nutrition#

Gets a random recipe.
See documentation for more info on parameters, but quickly:

limitLicense - set this to true if you want to limit to recipes with an attributed license (something like this).
for my purposes since this is just personal use I am leaving false.

number - the number of recipes to return.

tags - these go at the end, and seem to correspond to the tags that show in the beginning of the object
Like, "ketogenic" ? "vegetarian" ? Not 100% on how this works. For my purposes it will probably be limited to 
vegetarian and dinner
*/

function getRandomRecipe(){
	$response = Unirest\Request::get("https://spoonacular-recipe-food-nutrition-v1.p.mashape.com/recipes/random?limitLicense=false&number=1&tags=vegetarian",
  		array(
    		"X-Mashape-Key" => "cRWKW5XML6mshRcU0OjwWDuF9k4fp1GmpVqjsnZmfp3yTZ2gsy",
    		"Accept" => "application/json"
  		)
	);
	return $response;
	
}

function getIngredients($recipe){
	$ingredients = $recipe->body->recipes[0]->extendedIngredients;
	$numberOfIngredients = count($ingredients);
	$listOfIngredients = array(); 
	#this can be an array I guess. I'm not sure if it's needed since the $ingredients
	#variable above is kind of the same. but this array or list or whatever may be easier to use? maybe I just need to leanr php better

	#echo $numberOfIngredients;

	for($counter=0; $counter<$numberOfIngredients; $counter++){
		#print_r($recipe->body->recipes[0]->extendedIngredients[$counter]->name);
		$listOfIngredients[] = $recipe->body->recipes[0]->extendedIngredients[$counter]->name;
		#echo "\n";
	}
	return $listOfIngredients;

}

function createIngredientString($ingredients){
	#take ingredient list
	#so you can either make the string yourself or make it with spaces and use urlencode.
	#idk php well enough but I think just using %2c is going to be okay and cleaner
	# example: $ingredientsString = "&ingredients=apples%2Cflour%2Csugar";
	 
	$noOfIngredients = count($ingredients);
	#echo $noOfIngredients;
	$ingredientString = '';
	for($counter=0; $counter<$noOfIngredients; $counter++){
		/*
		this was the way I tried to manually do urlencode. think I can prob delete
		if ($noOfIngredients>1) {
			#echo $ingredients[$counter];
			#if(word has a space in it, replace the space with a +)
			# this is done through urlencode. I think we don't need the %2c, but not sure how urlencode will handle
			# maybe this is why the other version exists
			$ingredientString = $ingredientString.$ingredients[$counter]."%2C";
		}
		else {
			$ingredientString = $ingredients[$counter];
			echo 'no you idiot!';
		}
	
	
		*/
		$ingredientString = $ingredientString.$ingredients[$counter].',';
	}
	
	$ingredientString = urlencode($ingredientString);
	return $ingredientString;
}


function getSimilarRecipes($ingredientString){
	/*
	I think it is worth putting in notes from the documentation as I did
	with the random recipe finder above. 
	
	Since I'm just focused on one part of the string for now, I think it's okay.
	*/
	
	
	# have to replace string in below line with "ingredientString"
	$otherRecipes = Unirest\Request::get("https://spoonacular-recipe-food-nutrition-v1.p.mashape.com/recipes/findByIngredients?fillIngredients=false&ingredients=apples%2Cflour%2Csugar&limitLicense=false&number=5&ranking=1",
  	array(
    	"X-Mashape-Key" => "cRWKW5XML6mshRcU0OjwWDuF9k4fp1GmpVqjsnZmfp3yTZ2gsy",
    	"Accept" => "application/json"
  	)
	);
	return $otherRecipes;

}

function getSimilarRecipesTest($ingredientString){
	

	# have to replace string in below line with "ingredientString"
	$urlString = 'https://spoonacular-recipe-food-nutrition-v1.p.mashape.com/recipes/findByIngredients?fillIngredients=false&ingredients='.$ingredientString.'&limitLicense=false&number=5&ranking=1';
	$recipesByIngredients = Unirest\Request::get($urlString,
  	array(
    	"X-Mashape-Key" => "cRWKW5XML6mshRcU0OjwWDuF9k4fp1GmpVqjsnZmfp3yTZ2gsy",
    	"Accept" => "application/json"
  	)
	);
	
	$otherRecipes = array();
	$recipes = $recipesByIngredients->body;
	$numberOfRecipes = count($recipes);
	#print_r($recipes[0]->title);
	
	#it does feel a bit like I am disassembling for the name for no reason. I could just reference
	# the right item in the array...revisit this
	for($counter=0; $counter<$numberOfRecipes; $counter++){
		#print_r($recipes[$counter]->title);
		$otherRecipes[$counter]['recipe_id'] = $recipes[$counter]->id;
		$otherRecipes[$counter]['recipe_name'] = $recipes[$counter]->title;
		
		
		#echo "\n";
	}
	
	

	return $otherRecipes;
	
}

/* For now I am just going to use this to return the link */
function getRecipeWebsite($id){
	$response = Unirest\Request::get('https://spoonacular-recipe-food-nutrition-v1.p.mashape.com/recipes/'.$id.'/information?includeNutrition=false',
  array(
    "X-Mashape-Key" => "cRWKW5XML6mshRcU0OjwWDuF9k4fp1GmpVqjsnZmfp3yTZ2gsy",
    "Accept" => "application/json"
  		)
	);
	
	return $response->body->sourceUrl; 


}

function printRecipeName($recipes){
	if(isset($recipes->body->recipes[0]->title)){
		echo "yes yes yes"; #for debugging
		for ($counter=0; $counter < count(recipes); $counter++){
		print_r($recipes->body->recipes[0]->title);
		}
	}
	
	else{
		#echo "no no no"; #for debugging
		foreach($recipes as $recipeInfoArray){
			#print_r($recipeInfoArray);
			echo($recipeInfoArray['recipe_name']);
			echo("\n");
			echo(getRecipeWebsite($recipeInfoArray['recipe_id']));
			echo("\n");
		
    	}
		
	}

	
	
	#echo "\n";

}





/* Body of the code. Maybe later on this can be it's own class and we'll just include it */

# going to comment these out a line at a time, so when I actually test I'll make sure each one works individually
# as opposed to having to debug what is breaking my code

$randomRecipe = getRandomRecipe();
#print_r($randomRecipe);
#printRecipeName($randomRecipe);

$ingredients =  getIngredients($randomRecipe);
#ingredients = array('Baseball', 'Milk and Cookles', 'Hamburger Meat', 'Butter', 'Milk');
#echo count($ingredients);

$ingredientString = createIngredientString($ingredients);
echo $ingredientString;
$similarRecipes = getSimilarRecipesTest($ingredientString);
# print for debugging
print_r($similarRecipes);
#echo count($similarRecipes);
printRecipeName($similarRecipes);
#echo $similarRecipes;
