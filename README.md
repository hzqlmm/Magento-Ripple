Magento-Ripple
==============

Ripple wallet payment method for Magento Commerce.
Please donate XRP to rBdC31XbhuivNWaAvQVyTuitmS3LZNEsQj

Redirect issues
===============

If you run into trouble redirecting users to the Ripple payment page, 
make sure to copy the extension's layout and template files to your 
custom theme. This is only necessary if your custom theme lives outside
of base/ or default/ themes.

Copy:
/app/design/frontend/default/default/template/appmerce/ripple/*.*
/app/design/frontend/default/default/layout/appmerce/ripple/*.*

To:
/app/design/frontend/MYTHEME/MYSUBTHEME/template/appmerce/ripple/*.*
/app/design/frontend/MYTHEME/MYSUBTHEME/layout/appmerce/ripple/*.*

(Change 'MYTHEME/MYSUBTHEME' accordingly.)
