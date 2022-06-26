<?php 
class PersonalCertificate{
    private static $DIRECTORY = null;
    private static $PATHTEMPLATE = null;
    private static $PATHFONT = null;
    private static $DATAFIELDPAINTNAME = null;
    private static $DATAFIELDPAINTCOURSE = null;
    private static $DATAFIELDPAINTDATE = null;
    public function __construct(string $name_template,string $name_font,array $dataFieldPaintName,array $dataFieldPaintCourse,array $dataFieldPaintDate)
    {
        PersonalCertificate::$DIRECTORY =  $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/OnlineEducation/certificates';
        if(!file_exists(PersonalCertificate::$DIRECTORY)){
            mkdir(PersonalCertificate::$DIRECTORY);
            mkdir(PersonalCertificate::$DIRECTORY.'/template');
        }
        if(!file_exists(PersonalCertificate::$DIRECTORY.'/template/'.$name_template)){
            mkdir(PersonalCertificate::$DIRECTORY.'/template/Ж');
            throw new Exception('Не найден файл шаблона');
        }
        if(!file_exists(PersonalCertificate::$DIRECTORY.'/template/'.$name_font)){
            throw new Exception('Не найден файл шрифта');
        }
        if(!isset($dataFieldPaintName['start_y'],$dataFieldPaintName['start_x'],$dataFieldPaintName['width'],$dataFieldPaintName['height'],$dataFieldPaintName['font-size'],$dataFieldPaintName['color']['red'],$dataFieldPaintName['color']['green'],$dataFieldPaintName['color']['blue'])){
            throw new Exception('Введённый массив dataFieldPaintName, не подходит к шаблону:'. print_r([
                'start_y' => null,
                'start_x' => null,
                'width' => null,
                'height' => null,
                'font-size' => null,
                'color' => [
                    "red" => null,
                    "green" => null,
                    "blue" => null
                ]
            ]));
        }
        if(!isset($dataFieldPaintCourse['start_y'],$dataFieldPaintCourse['start_x'],$dataFieldPaintCourse['width'],$dataFieldPaintCourse['height'],$dataFieldPaintCourse['font-size'],$dataFieldPaintCourse['color']['red'],$dataFieldPaintCourse['color']['green'],$dataFieldPaintCourse['color']['blue'])){
            throw new Exception('Введённый массив dataFieldPaintCourse, не подходит к шаблону:'. print_r([
                'start_y' => null,
                'start_x' => null,
                'width' => null,
                'height' => null,
                'font-size' => null,
                'color' => [
                    "red" => null,
                    "green" => null,
                    "blue" => null
                ]
            ]));
        }
        if(!isset($dataFieldPaintDate['day'],$dataFieldPaintDate['mounth'],$dataFieldPaintDate['year'],$dataFieldPaintDate['font-size'],$dataFieldPaintDate['color']['red'],$dataFieldPaintDate['color']['green'],$dataFieldPaintDate['color']['blue'])){
            throw new Exception('Введённый массив dataFieldPaintDate, не подходит к шаблону:'. print_r([
                'day' => null,
                'mounth' => null,
                'year' => null,
                'font-size' => null,
                'color' => [
                    "red" => null,
                    "green" => null,
                    "blue" => null
                ]
            ]));
        }
        if(!isset($dataFieldPaintDate['day']['x'],$dataFieldPaintDate['day']['y'])){
            throw new Exception('Элемент day введённого массива dataFieldPaintDate, не подходит к шаблону:'. print_r([
                'start_y' => null,
                'start_x' => null,
                'width' => null,
                'height' => null,
                'color' => null
            ]));
        }
        if(!isset($dataFieldPaintDate['mounth']['start_y'],$dataFieldPaintDate['mounth']['start_x'],$dataFieldPaintDate['mounth']['width'],$dataFieldPaintDate['mounth']['height'])){
            throw new Exception('Элемент mounth введённого массива dataFieldPaintDate, не подходит к шаблону:'. print_r([
                'x' => null,
                'y' => null,
            ]));
        }
        if(!isset($dataFieldPaintDate['year']['x'],$dataFieldPaintDate['year']['y'])){
            throw new Exception('Элемент year введённого массива dataFieldPaintDate, не подходит к шаблону:'. print_r([
                'x' => null,
                'y' => null,
            ]));
        }
        PersonalCertificate::$PATHTEMPLATE = PersonalCertificate::$DIRECTORY.'/template/'.$name_template;
        PersonalCertificate::$PATHFONT = PersonalCertificate::$DIRECTORY.'/template/'.$name_font;
        PersonalCertificate::$DATAFIELDPAINTNAME = $dataFieldPaintName;
        PersonalCertificate::$DATAFIELDPAINTCOURSE = $dataFieldPaintCourse;
        PersonalCertificate::$DATAFIELDPAINTDATE = $dataFieldPaintDate;
    }

    private static function WriteName(string $name,$image){
        if(PersonalCertificate::$DATAFIELDPAINTNAME == null){
            throw new Exception('Нет входных данных для рисования имени');
        }
        $degree = 0; // Угол поворота текста в градусах
        $center_x = PersonalCertificate::$DATAFIELDPAINTNAME['start_x'] + PersonalCertificate::$DATAFIELDPAINTNAME['width']/2 - strlen($name)/2*(PersonalCertificate::$DATAFIELDPAINTNAME['font-size']/2);
        $center_y = PersonalCertificate::$DATAFIELDPAINTNAME['start_y'] + PersonalCertificate::$DATAFIELDPAINTNAME['height']/2;
        $color = imagecolorallocate($image, (int)PersonalCertificate::$DATAFIELDPAINTNAME['color']['red'], (int)PersonalCertificate::$DATAFIELDPAINTNAME['color']['green'], (int)PersonalCertificate::$DATAFIELDPAINTNAME['color']['blue']); // Функция выделения цвета для текста
        imagettftext($image, PersonalCertificate::$DATAFIELDPAINTNAME['font-size'], $degree, $center_x, $center_y, $color, PersonalCertificate::$PATHFONT, $name); // Функция нанесения текста
        return $image;
    }
    private static function WriteDate(string $date,$image){
        if(PersonalCertificate::$DATAFIELDPAINTDATE == null){
            throw new Exception('Нет входных данных для рисования даты');
        }
        $newDate = date("d-m-Y", strtotime($date));
        if($newDate === '01-01-1970'){
            throw new Exception("Дата, переданная в качестве параметров имеет неверный формат");
        }
        $day = date("d", strtotime($newDate));
        $mounth = date("m", strtotime($newDate));
        $year = date("y",strtotime($newDate));
        $degree = 0; // Угол поворота текста в градусах
        #region Запись месяца
        $center_x_mounth = PersonalCertificate::$DATAFIELDPAINTDATE['mounth']['start_x'] + PersonalCertificate::$DATAFIELDPAINTDATE['mounth']['width']/2 - (int)PersonalCertificate::$DATAFIELDPAINTDATE['font-size']/2;
        $center_y_mounth = PersonalCertificate::$DATAFIELDPAINTDATE['mounth']['start_y'] + PersonalCertificate::$DATAFIELDPAINTDATE['mounth']['height']/2;
        $color = imagecolorallocate($image, (int)PersonalCertificate::$DATAFIELDPAINTDATE['color']['red'], (int)PersonalCertificate::$DATAFIELDPAINTDATE['color']['green'], (int)PersonalCertificate::$DATAFIELDPAINTDATE['color']['blue']); 
        imagettftext($image, PersonalCertificate::$DATAFIELDPAINTDATE['font-size'], $degree, $center_x_mounth, $center_y_mounth, $color, PersonalCertificate::$PATHFONT, $mounth); 
        #endregion
        #region Запись дня
        imagettftext($image, PersonalCertificate::$DATAFIELDPAINTDATE['font-size'], $degree, PersonalCertificate::$DATAFIELDPAINTDATE['day']['x'], PersonalCertificate::$DATAFIELDPAINTDATE['day']['y'], $color, PersonalCertificate::$PATHFONT, $day); 
        #endregion
        #region Запись года
        imagettftext($image, PersonalCertificate::$DATAFIELDPAINTDATE['font-size'], $degree, PersonalCertificate::$DATAFIELDPAINTDATE['year']['x'], PersonalCertificate::$DATAFIELDPAINTDATE['year']['y'], $color, PersonalCertificate::$PATHFONT, $year); 
        #endregion
        return $image;

    }
    private static function WritenameCourse($nameCourse,$image){
        if(PersonalCertificate::$DATAFIELDPAINTCOURSE == null){
            throw new Exception('Нет входных данных для рисования имени');
        }
        $degree = 0; // Угол поворота текста в градусах
        $center_x = PersonalCertificate::$DATAFIELDPAINTCOURSE['start_x'] + PersonalCertificate::$DATAFIELDPAINTCOURSE['width']/2 - strlen($nameCourse)/2*((int)PersonalCertificate::$DATAFIELDPAINTCOURSE['font-size']/2);
        $center_y = PersonalCertificate::$DATAFIELDPAINTCOURSE['start_y'] + PersonalCertificate::$DATAFIELDPAINTCOURSE['height']/2;
        $color = imagecolorallocate($image, (int)PersonalCertificate::$DATAFIELDPAINTCOURSE['color']['red'], (int)PersonalCertificate::$DATAFIELDPAINTCOURSE['color']['green'], (int)PersonalCertificate::$DATAFIELDPAINTCOURSE['color']['blue']); // Функция выделения цвета для текста
        imagettftext($image, PersonalCertificate::$DATAFIELDPAINTCOURSE['font-size'], $degree, $center_x, $center_y, $color, PersonalCertificate::$PATHFONT, $nameCourse); // Функция нанесения текста
        return $image;
    }
    public static function Create($name,$nameCourse,$dateCreate):string {
        if(!file_exists(PersonalCertificate::$PATHTEMPLATE)){
            throw new Exception('Не найден шаблон сертификата');
        }
        if(!file_exists(PersonalCertificate::$PATHFONT)){
            throw new Exception('Не найден шрифт сертификата');
        }
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/OnlineEducation/certificates/certificate.jpg';
        if(file_exists($filePath)) {
            unlink($filePath);
        }
        $image = imagecreatefromjpeg(PersonalCertificate::$PATHTEMPLATE);
        $image = PersonalCertificate::WriteName($name,$image);
        $image = PersonalCertificate::WritenameCourse($nameCourse,$image);
        $image = PersonalCertificate::WriteDate($dateCreate,$image);
        $nameFile = 'certificate.jpg';
        imagejpeg($image, PersonalCertificate::$DIRECTORY."/".str_replace(' ', '_', $nameFile)); // Сохранение рисунка
        imagedestroy($image); // Освобождение памяти и закрытие рисунка
        return get_template_directory_uri() . "/certificates/". str_replace(' ', '_', $nameFile); }
}