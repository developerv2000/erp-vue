# New version differences:

## Attachments
- file_path => folder
- file_type => removed!
- file_size => file_size_in_mb // REQUIRES RECALCULATING!
    string => decimal(6,2)

## Atx
- Added unique for 'inn_id' and 'form_id'

## Processes
- order_priority => days_past_since_last_activity

## Misc
- Permissions fully updated. Reattach users direct permission manually
- Fix uploading attachments for muptiple "products" create.
