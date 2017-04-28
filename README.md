# tmsconnect
TMS connectivity plugin

TMS has 10 modules that are supported in the DB. This plugin attempts to generically integrate TMS into a WordPress friendly data architecture that allows for easy theme integration and search using ElasticSearch and the SearchPress plugin. TMS module imports are handled by individual database processors. Each processor is defined by custom SQL statements that suit individual TMS systems.


### Object Module
At it's core, TMSConnect pulls in TMS objects and creates corresponding WP Object Post Types. Object information includes the object name, material, object number, description, curatorial notes, classification, dates, dimensions, provenience and current storage locations. Information that can be used as a classifier such as `material` or `classification` will be stored as a generic taxonomy. Otherwise, it will be added as post meta data.

### Constituents Module
Constituents are defined as a custom heiarchical taxonomy and contains the detailed information regarding any person, group or entity who has handled an object within a collection at any point in the object’s life. This includes the artist or maker’s information, donor’s information, auction house information, other museum’s information and staff information.

### Media Module
TMS media is processed as WordPress attachments.

### Exhibitions Module
Exhibitions are implemented as a custom WP Object that has a custom post type `exhibiton` and a custom taxonomy `exhibitions` defined. In creating a new exhibiton WP Post Type, a correspondig taxonomy is created of the same name. The exhibition post type can be assigned an exhibition type such as in-house exhibition, traveling exhibition, virtual exhibition or public information. Documentation, notes, media and bibliographical information of the exhibition are stored as post meta data.

### Loans Module
Loans are currently implemented loosely as a Zoninator Zone and this needs further development.

### Shipping Module
This has not been implemented and needs further development.

### Bibliography Module
This module has been integrated into the object post type as post meta and needs further development.

### Events Module
A custom post type has been defined for events but needs further development.

### Sites Module
This has been implemented as a custom taxonomy and needs further development.

### Insurance Module
This exists as object post meta data and needs further development.