# fapi-wp-client-section
FAPI client section plugin for WP.


# WP API
HTTP GET {domain}/fapi-wp-client-section.php?getSections

HEADERS: \
content-type: application/json \
accept: application/json 

returns object, where key is section ID and value is section name
```json
{
    "data": {
        "1": "section one",
        "2": "section two",
        "3": "section three"
    }
}
```
