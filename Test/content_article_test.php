<?php
/**
 * @author Artjom Kurapov
 * @since 11.03.12 23:21
 */

/**
 * @property \Gratheon\CMS\Model\Article $object
 */
class content_article_test extends PHPUnit_Framework_TestCase{
    public function setUp(){
        //$this->getMock('Model', array('q','__construct','Model'),array(),'',false,false,false);
        $this->getMock('\Gratheon\Core\Model');
        $this->getMock('\Gratheon\Core\Record');

        require_once '../Model/Article.php';
        $this->object = new \Gratheon\CMS\Model\Article(true);
    }

    public function encodeImages(){
        $r = $this->object->encodeImages('
<p>Heap это просто такое дерево, ключи элементов которого распределяются по старшинству. Кучи часто используются в алгоритмах с графами. В php для этого есть SplHeap, SplMaxHeap и SplMinHeap, которые в сотни раз быстрей массивов.</p>

<p><img style="float: right; margin: 0pt 0pt 10px 10px;" rel="6618" src="http://kurapov.name/res/image/thumb/1942.png" alt="Куча" title="Куча"></p>

<p>Обобщение кучи это очередь с приоритетом - SplPriorityQueue, где порядок не FIFO/LIFO, а как вы догадались, согласно заданной важности</p>
        ',5554);


        $this->assertEquals('
<p>Heap это просто такое дерево, ключи элементов которого распределяются по старшинству. Кучи часто используются в алгоритмах с графами. В php для этого есть SplHeap, SplMaxHeap и SplMinHeap, которые в сотни раз быстрей массивов.</p>

<p><!--image[6618]--float: right; margin: 0pt 0pt 10px 10px;--></p>

<p>Обобщение кучи это очередь с приоритетом - SplPriorityQueue, где порядок не FIFO/LIFO, а как вы догадались, согласно заданной важности</p>
        ',$r);
    }


    public function decodeImages(){

        $content_menu = $this->getMock('content_menu',array('singleton'));
        $this->getMock('content_image',array('singleton','obj'));

print_r($this->object->decodeImages('<p>Heap это просто такое дерево, ключи элементов которого распределяются по старшинству. Кучи часто используются в алгоритмах с графами. В php для этого есть SplHeap, SplMaxHeap и SplMinHeap, которые в сотни раз быстрей массивов.</p>

        <p><!--image[6618]--float: right; margin: 0pt 0pt 10px 10px;--></p>

        <p>Обобщение кучи это очередь с приоритетом - SplPriorityQueue, где порядок не FIFO/LIFO, а как вы догадались, согласно заданной важности</p>'));


    }


    /**
     * @test
     */
    public function encodeEmbeddables(){
        $r = $this->object->encodeEmbeddables('<p>В школе рассказывают как люди постепенно открывали химические элементы и то, как Менделеев сгруппировал атомы по своим характеристикам в ряды (по числу стабильных орбиталей) и группы (по числу электронов на внешней орбитали), но как только дело касается спина или детальных уравнений, то извините - тут всякие тензоры, вы ещё не доросли.&nbsp;</p>


        <p>Если нагревать материю, то излучается свет, при этом экспериментально установлена зависимость температуры материала от цвета (длины волны). В то время был выведен&nbsp;<i>второй закон смещения Вина</i>&nbsp;которые примерно указывает зависимость между параметрами</p>

        <p>Классическая физика выводила и формулу Релея-Джинса, приводящая к парадоксу, <i>ультрафиолетовой катастрофе</i>&nbsp;- сильно нагретые тела (скажем при 5000 кельвинов) выделяли бы огромную энергию, расходящуюся с наблюдениями</p>
        <div rel="7152" class="embed embed_formula">f(omega, T) = kT frac{omega^2}{4pi^2c^2}</div>

        <p>В 1900 г. Планк разрешил это противоречие введя квантование - дискретное выделение энергии (при постоянной температуре на единицу поверхности) с участием постоянной Планка (h).</p>');

        echo $r;

    }

    /**
     * @test
     */
    public function encodeEmbeddablesMultiline(){
        $r = $this->object->encodeEmbeddables('<p>a</p>

        <div rel="7493" class="embed embed_code">b
        c</div>

        <h3>Вариации</h3>');

        echo $r;

    }
    /**
     * @test
     */
    public function encodeEmbeddablesMultiEntity(){
        $r = $this->object->encodeEmbeddables('
        <h3>Инструменты</h3>


        <ul>

        	<li>Ручками из веб-консоли</li>

        	<li><a href="http://docs.amazonwebservices.com/AutoScaling/latest/DeveloperGuide/astools.html">AWS Autoscaling</a>&nbsp;<br>

        <div rel="7490" class="embed embed_video"><img src="http://i.ytimg.com/vi/ainDIPzVM84/0.jpg" unselectable="on" style="cursor: default; "></div>

        </li></ul>

        <h3>Кластерный деплой</h3>


        <ul>

        	<li>пофайловое обновление</li>

        	<li>checkout</li>

        	<li>символическая ссылка на стабильное</li>

        	<li>package manager</li>
        </ul>
        <div><div rel="6337" class="embed embed_video"><img src="http://i.ytimg.com/vi/2vA2Yzv-NoI/0.jpg">
           </div>
        </div>
        <p>яваыва</p>');

        echo $r;

    }


    /**
     * @test
     */
    public function decodeEmbeddables(){
        $r = $this->object->decodeEmbeddablesForPublic('<p>Если нагревать материю, то излучается свет, при этом экспериментально установлена зависимость температуры материала от цвета (длины волны). В то время был выведен&nbsp;<i>второй закон смещения Вина</i>&nbsp;которые примерно указывает зависимость между параметрами</p>

                <p>Классическая физика выводила и формулу Релея-Джинса, приводящая к парадоксу, <i>ультрафиолетовой катастрофе</i>&nbsp;- сильно нагретые тела (скажем при 5000 кельвинов) выделяли бы огромную энергию, расходящуюся с наблюдениями</p>
                <!--embed[7152]-->

                <p>В 1900 г. Планк разрешил это противоречие введя квантование - дискретное выделение энергии (при постоянной температуре на единицу поверхности) с участием постоянной Планка (h).</p>');

        echo $r;

    }
}