<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
define('IBLOCK_ID', 13);
define('DATA_FILE', "test.csv");
define('OLD_FILE', "test1.csv");

if (fileNotChanged()) {

    echo 'files are equal';
} else {
    // Подключение модуля "Информационные блоки"
    CModule::IncludeModule('iblock');
    // Получает данные из файла 'DATA_FILE'
    $data = getDataFromFile(DATA_FILE);

    // Перебирает массив, задаваемый с помощью $data и на каждой итерации значение текущего элемента присваивается переменной $item.
    foreach($data as $item) {
        // Ищет элемент в битриксе по ID,  сохраненному в поле CODE
        if ($existing = findElementByCode($item[0])) {
            $id = $existing->fields["ID"];
            // Обновляет элемент
            updateElement($id, $item);
            echo "element updated<br>";
        } else {
            // Иначе добавляет элемент
            addElement($item);
            echo "element added<br>";
        }

    }
    // запоминает файл, чтобы проверить его в след. раз
    rememberFile();    
}

/********** ФУНКЦИИ ***********/

// Сравнивает новый и старый файл(проверка на внесенение изменений в файл)
function fileNotChanged() {
    return file_exists(OLD_FILE) && md5_file(DATA_FILE) === md5_file(OLD_FILE);
}

// Копирует файл в другой файл
function rememberFile() {
    copy(DATA_FILE, OLD_FILE);
}

// Читает содержимое файла в строку, удаляет пробелы или символы из начала и конца строки м разбивает файл по регулярному выражению (переносы строк) 
function getDataFromFile($file) {
    $csv = trim(file_get_contents($file));
    $data = preg_split('/\n+/', $csv);

    $arr = array();
    foreach ($data as $value) {
        $arr[] = str_getcsv($value, ';');
    }
    unset($arr[0]); // убираем строку заголовков
    return $arr;
}

// Пробует найти уже добавленный ранее элемент по полю CODE, чтобы обновить его
function findElementByCode($code) {
    $arFilter = ["CODE" => $code];
    $res = CIBlockElement::GetList(Array(), $arFilter);
    return $res->GetNextElement();
}

// Добавление элемента в битрикс
function addElement($item) {
    $el = new CIBlockElement();
    global $USER;
    return $el->Add([
        'MODIFIED_BY' => $USER->GetID(),
        'IBLOCK_SECTION_ID' => false,
        'IBLOCK_ID' => IBLOCK_ID,
        'CODE' => $item[0],
        'NAME' => $item[1],
        'PROPERTY_VALUES' => [
            'PREVIEW_TEXT' => $item[2],
            'DETAIL_TEXT' => $item[3],
            'PROP1' => $item[4],
            'PROP2' => $item[5],
        ],
        'ACTIVE' => 'Y',
    ]);
}

// Обновление элемента в битриксе
function updateElement($id, $item) {
    $el = new CIBlockElement();
    $arrUpdate = [
        'NAME' => $item[1],
        'PROPERTY_VALUES' => [
            'PREVIEW_TEXT' => $item[2],
            'DETAIL_TEXT' => $item[3],
            'PROP1' => $item[4],
            'PROP2' => $item[5],
        ],
    ];
    return $el->Update($id, $arrUpdate);
}



