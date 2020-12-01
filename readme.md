[dev.innomuebles.com/magentonew](https://dev.innomuebles.com/magentonew) (Magento 2).

## How to deploy the static content 
```shell                 
rm -rf pub/static/*
bin/magento setup:static-content:deploy \
	--area adminhtml \
	--theme Magento/backend \
	-f en_US es_CR
bin/magento setup:static-content:deploy \
	--area frontend \
	--theme MageBig/martfury_layout04 \
	-f en_US es_CR
bin/magento cache:clean
```