<?php

function ClassifiedAds_categoriesGetAll() {
	return dbAll('select * from classifiedads_categories order by name');
}
