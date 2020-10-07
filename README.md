## Forms Results for Evolution CMS

Сниппет для сохранения значений форм при их отправке и модуль для вывода архива заявок.

Для сохранения результатов нужно добавить в вызов `FormLister` параметр 
```
&prepareAfterProcess=`CatchFormResult`
```

Для вывода результатов нужно переименовать файлы конфигурации `assets/modules/formresults/config/*.php.sample` в `*.php` либо создать свои.

![2020-10-07_23-42-08](https://user-images.githubusercontent.com/8789957/95374017-3ee3e880-08f7-11eb-9795-4a17bbc3a8a4.png)
