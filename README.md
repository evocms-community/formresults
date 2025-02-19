## Forms Results for Evolution CMS

Решение для сохранения полей отправленных форм.

### Подключение
Добавить в вызов `FormLister` параметр 
```
&prepareAfterProcess=`CatchFormResult`
```
### Настройка модуля
Переименовать файлы конфигурации `assets/modules/formresults/config/*.php.sample` в `*.php` либо создать свои конфигурации.

**Названия файлов конфигурации должны совпадать с идентификаторами форм (formid).**

![2020-10-07_23-42-08](https://user-images.githubusercontent.com/8789957/95374017-3ee3e880-08f7-11eb-9795-4a17bbc3a8a4.png)

### Экспорт в Эксель
Для экспорта необходимо установить библиотеку PhpSpreadsheet:
* Открыть консоль, зайти в папку `/core`
* Выполнить команду:
```
composer require phpoffice/phpspreadsheet
```
* В модуле появится кнопка "Экспорт в XLS"
* ![image](https://github.com/user-attachments/assets/e2a768ff-225e-43f6-9a6b-d7c013b262aa)
