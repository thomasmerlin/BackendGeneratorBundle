# Todo List :

#### EasyAdminBundle :

- Structure :
    - Main EasyAdmin's package file path : ``config/packages/easy_admin.yaml``
        - This file only has an import of the resource coming from the ``config/packages/easy_admin`` directory.
    - Path to insert the EasyAdmin's structure: ``config/packages/easy_admin``
    - Level structure :
        - ``entities/``
            - ``my_entity.yaml``
        - ``menu.yaml`` : Backoffice's side menu configuration. (Note : Do not forget that the menu is basically part
         of the design according to EasyAdminBundle's configuration, just putting it aside to avoid hundreds of lines
         in the same file)
        - ``design.yaml`` : Backoffice's design configuration.

- Commands :
    - Generate the basic EasyAdmin's structure :
        - TODO LIST :
            - Checking if EasyAdminBundle is well registered.
            - Generating the main EasyAdmin's package file
            - Creating the ``config/packages/easy_admin/entities`` directory :
                - Also adding a ``.gitkeep`` file inside
            - Creating the ``config/packages/easy_admin/menu.yaml`` : 
                - Ask the user to enter the next item's label
                    - Null value : Go to parsing generation of the file
                - Ask if the item has children
                    - Yes : Make a loop and ask the user for :
                        - Label
                            - Null value : End the loop and go to the next item
                        - Entity
                        - Icon
                    - No : Ask the user for : 
                        - Entity
                        - Icon
                - Generate correctly the file with the Yaml Component
            - Creating the ``config/packages/easy_admin/design.yaml`` :
                - Ask the user to enter the site name
                - Generate correctly the file with the Yaml Component
              
