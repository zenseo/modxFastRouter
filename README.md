##MODX Fast Router

Используется вот эта замечательная библиотека https://github.com/nikic/FastRoute

Объявлять маршруты нужно в чанке **fastrouter** в таком формате:

```json
[
    ["GET","/fastrouter/{name}/{id:[0-9]+}","1"],
    ["GET","/fastrouter/{id:[0-9]+}","1"],
    ["GET","/hello/{name}","1"],
    ["GET","/contact","1"]
]
```

- Первый параметр `GET` метод запроса, может быть `GET`, `POST`.
- Второй параметр `/fastrouter/{name}/{id:[0-9]+}` наш маршрут, где `{name}` именованный параметр принимающий любые символы, `{id:[0-9]+}` именованный параметр принимающий только цифры. Можно использовать любые валидные регулярные выражения.
- Третий параметр `2` ID ресурса куда будет направлен запрос.

Если запрошенному URL не соответствует ни один объявленный маршрут, будет сгенерированна 404 ошибка.

Все именнованные параметры попадут в массив `fastrouter` в глобальном массиве `$_REQUEST`. В нашем случае по первому маршруту, например `http://site.com/fastrouter/vanchelo/10` получим вот такие данные:
```php
var_dump($_REQUEST);

Array
(
    [q] => user/vanchelo/10
    [fastrouter] => Array
        (
            [name] => vanchelo
            [id] => 10
        )
)
```

По умолчанию имя ключа со всеми параметрами маршрута - `fastrouter`.
Для задания своего ключа измените в настройках системы параметр `fastrouter.paramsKey`.

Скачать готовый пакет здесь https://www.dropbox.com/sh/8d2eamq9tybkqz4/AAAH00ZP6gp51EFLI7TMrzxAa?dl=0
