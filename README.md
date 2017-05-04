# MMCmfAdminBundle
This addon is meant to be installed into a customAdmonBundle which is based on MMAdminBundle.
It integrates the MMCmfContentBundle/Page-Entity into the admin backend.

### Append to app/AppKernel.php

```
...
    public function registerBundles()
    {
        ....
        $bundles = array(
            ...
           new MandarinMedien\MMCmf\Admin\PageAddonBundle\MMCmfAdminPageAddonBundle(),
           ...
        );
        ....
    }
...
```

### Append to src/MY_CUSTOM_ADMIN_BUNDLE/config/routing.yml

```
...

mm_cmf_page_addon:
    resource: "@MMCmfAdminPageAddonBundle/Resources/config/routing.yml"
    prefix:   /page

...
```