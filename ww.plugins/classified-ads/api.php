<?php

// { ClassifiedAds_categoryTypesGet

/**
	* get prices for ads
	*
	* @return array
	*/
function ClassifiedAds_categoryTypesGet() {
	return dbAll('select * from classifiedads_types order by name');
}

// }
// { ClassifiedAds_categoriesGetAll

/**
	* get list of categories
	*
	* @return array
	*/
function ClassifiedAds_categoriesGetAll() {
	return dbAll('select * from classifiedads_categories order by name');
}

// }
