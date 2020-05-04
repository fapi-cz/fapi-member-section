# FAPI member section
FAPI member section plugin for WP.


# WP API
HTTP GET {domain}/fapi-member-section.php

HEADERS: \
content-type: application/json \
accept: application/json 

returns object, where key is member section ID and value is member section name
```json
{
    "data": {
        "1": "section one",
        "2": "section two",
        "3": "section three"
    }
}
```
