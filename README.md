## Forms Results for Evolution CMS

Сниппет для сохранения значений форм при их отправке и модуль для вывода архива заявок.

Для сохранения результатов нужно добавить в вызов `FormLister` параметр 
```
&prepareAfterProcess=`CatchFormResult`.
```

Для вывода результатов нужно переименовать файлы конфигурации `assets/modules/formresults/config/*.php.sample` в `*.php` либо создать свои.
