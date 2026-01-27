# Required fixes:
- Fix uploading attachments for multiple "Products" create
- User model, fix 'productSearches relation/count' after adding 'ProductSearch'
- Add :return typos for controller functions
- Refactor Products 'getMatchedProductSearchesAttribute()'
- Refactor MAD KPI addActiveManufacturersCountsForMonths() when country_id is filled
- Use request()->safe() for PLD/CMD and other requests instead of request->all
- Optimize Orders/OrderProduct scopeWithBasicPLDRelations/scopeWithBasicPLDRelationCounts/appendBasicPLDAttributes
etc functions
- Add 'loadMissing' relations for all models 'append' functions, as added in Shipment model.
- NO EDIT/UPDATE for import.products. Remove redundant request/permissions if not required!
- IMPORTANT: Optimize Process model events. Move most funtions into update/create from request functions
and also Manufacturer models saving event!
- Create 'removeRedundantAtxes' php artisan function
- Optimize loading relations and selecting only required attributes when detecting Order 
and OrderProduct models statuses (like OrderProduct::scopeWithOnlySelectsForDetectingStatus)
- Add 'arrived at warehouse filter' for Order and OrderProduct
