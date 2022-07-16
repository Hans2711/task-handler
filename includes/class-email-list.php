<?php


class EmailList
{
    /**
     * @var array emailList
     */
    private $emaiList;

    /**
     * @var string $json_file_path
     */
    private $json_file_path;

    public function __construct($json_file_path)
    {
        $this->emaiList = [];
        $file_content = file_get_contents($json_file_path);
        $file_decode = json_decode($file_content, true);
//        echo '<pre>';
//        die(var_dump($file_decode));
//        echo '</pre>';
        $this->emaiList = $file_decode;
        $this->json_file_path = $json_file_path;
    }


    public function update()
    {
        $file_content = json_encode($this->emaiList, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        file_put_contents($this->json_file_path, $file_content);
    }

    /**
     * @param array $data
     * @return void
     */
    public function addData($data)
    {
        $this->emaiList = array_merge($this->emaiList, $data);
        $this->update();
    }

    public function deleteData($var)
    {
        foreach ($this->emaiList as $key => $item) {
            foreach($item as $single_var) {
                if($single_var == $var) {
                    unset($this->emaiList[$key]);
                }
            }
        }
        $this->update();
    }

    public function getEmailList()
    {
        return $this->emaiList;
    }

}