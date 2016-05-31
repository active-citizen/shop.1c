
# Unit-тестирование (пройдено с ошибками)

* Неуспешных тестов тестов: 2;
* Успешных тестов: 2;

## Подробно о неуспешных тестах

    > not ok 2 - Failure: phpTest::testMbstringInternalEncoding  
    >   ---  
    >   message: 'Проверка mbstring.internal_encoding'  
    >   severity: fail  
    >   data:  
    >       got: UTF-8  
    >       expected: UTF-80  
    >   ...  

    > not ok 4 - Failure: phpTest::testPcreRecursionLimit  
    >   ---  
    >   message: 'Проверка pcre.recursion_limit'  
    >   severity: fail  
    >   data:  
    >       got: '10000'  
    >       expected: '100000'  
    >   ...  
