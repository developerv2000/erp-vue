# New version differences:

## Attachments
- file_path => folder
- file_type => removed!
- file_size => file_size_in_mb // REQUIRES RECALCULATiON!
    string => decimal(6,2)

## Atx
- Added unique for 'inn_id' and 'form_id'

## Processes
- order_priority => days_past_since_last_activity

## Misc
- Permissions fully updated. Reattach users direct permission manually
- Fix uploading attachments for multiple "Products" create
- Fix moving to the top FilterBooleanAutocomplete when selected "false" value
- User model, fix 'productSearches relation/count' after adding 'ProductSearch'
- Add :return typos for controller functions
- Refactor Products 'getMatchedProductSearchesAttribute()'
- Refactor MAD KPI addActiveManufacturersCountsForMonths() when country_id is filled
- Use request()->safe() for PLD/CMD and other requests instead of request->all
- removeDateTimezonesFromFormData from MAD part
- Rename permissions like "receive-notification-when-CMD-invoice-is-sent-for-payment" to
"receive-notification-when-invoice-is-sent-for-payment-by-CMD"
- Optimize Orders/OrderProduct scopeWithBasicPLDRelations/scopeWithBasicPLDRelationCounts/appendBasicPLDAttributes
etc functions
