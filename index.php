<script>


  //**********************
  //*
  //* Edit this part to configure behavior on click item type
  //*
  //*********************

  var click_musics = function({index, item}) {
    return {
      setup() {
        item = ref(item);
        console.log(item.value);
        return {
          item
        }
      },
      template: `
      <div>
      <audio
          controls
          :src="'./'+item.path">
              Your browser does not support the
              <code>audio</code> element.
      </audio>
      </div>
      `
    }
  }

  var click_documents = function({index, item}) {
    return {
      setup() {
        item = ref(item);
        console.log(item.value);
        return {
          item
        }
      },
      template: `
      <div>
      {{ item.path }}
      </div>
      `
    }
  }
  //**********************
  //*********************

</script>



<?php

$CONFIG = file_get_contents("config.json");
$CONFIG = json_decode($CONFIG, true);

session_start();	
ini_set('display_errors',1);
error_reporting(E_ALL); 
setlocale (LC_TIME, 'en_EN.utf8','eng');

$FILES = array();
$default_dir = '.';


function scanDirectory($dir,$config,$results = array()) {
  if (is_dir($dir)) {
    $iterator = new RecursiveDirectoryIterator($dir);
    foreach ( new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file ) {
        if ($file->isFile()) {
            $thispath = str_replace('\\', '/', $file);
            if (!in_array($file->getFileName(), $config['rules']['_ignore'])) {
            $thisfile = utf8_encode($file->getFilename());
            $results = array_merge_recursive($results, pathToArray($thispath));
            }
        }
    }
    return $results;
}
}
$results = array();
$FILES = scanDirectory($default_dir,$CONFIG, $results);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $CONFIG['appName']; ?></title>
  <script src="./lib.js"></script>
  <link rel="stylesheet" href="./style.css">
</head>
<body>
  <div id="app" :style="config.styles.app">
    <div class="container" :style="config.styles.app">
      <div class="logo">
        <img src="./logo.png">
      </div>
      <div
        class="bt"
        v-for="(type,nameType) in files"
        :key="'type'+nameType"
        :style="config.styles[nameType]"
      >
        <div class="type"> {{ nameType }} <span>- {{ type.length }} files </span></div>
        <div class="content">
        <ul class="list">
          <li v-for="(file,index) in type" :key="'file'+type+index" v-on:click="onClickElement(nameType,file,index)">
            <div class="icon"></div>
            <div class="name">{{ file.name }}</div>
            <div class="function" v-if="activeItem === nameType+index">
                <component :is="activeComponent" style="width: 100%"/>
            </div>
          </li>
        </ul>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

<script>

  const libs = window;
  const { 
    createApp,
    onMounted,
    ref,
    reactive,
    shallowRef,
  } = Vue;

  const App = {

    setup() {
      var filesFromPHP = '<?= json_encode($FILES); ?>';
      var config = JSON.parse('<?= json_encode($CONFIG); ?>');
      const organizedFiles = {};
      filesFromPHP = JSON.parse(filesFromPHP);
      filesFromPHP = filesFromPHP["."];
      files = libs._organizeFiles(config.rules,filesFromPHP);
      files = reactive(files);
      config = reactive(config);
      var activeItem = ref(null);
      var activeComponent = shallowRef(null);
      onClickElement = (type,item,index) => {
        console.log(type);
        console.log(typeof config.actions[type]);
        if (typeof config.actions[type] === "string" && typeof window['click_'+type] === "function") {
          activeItem.value = type+index
          activeComponent.value = window['click_'+type]({item, index})
        }
      }

      console.log(files);
      console.log(config);
      console.log(config.styles.app);
      return {
        activeItem, 
        activeComponent,
        files,
        config,
        onClickElement,
      };
    },

    // template: `<h1>Hello {{ name }}</h1>`,
  };

  createApp(App).mount("#app");
</script>

<?php

// Utilities functions

function pathToArray($path , $separator = '/') {
    if (($pos = strpos($path, $separator)) === false) {
        return array($path);
    }
    return array(substr($path, 0, $pos) => pathToArray(substr($path, $pos + 1)));
}

function replaceParasite($string) {

  $parasites = [
    "./","../",".\\",".\\\\"
  ];
  foreach($parasites as $parasite) {
    $string = str_replace($parasite, '', $string);
  }
  return $string;
}

?>

<style>
#app {
  display: flex;
  flex: 1;
  flex-direction: column;
}

#app .container {
  flex: 1;
  padding: 0; margin: 0;
}
#app .container .logo {
  width: 100%;
  height: auto;
  text-align: center;
  padding: 0; margin: 0;
}
#app .container .logo img {
  max-width: 100%;
  height: auto;
}
#app .bt {
display: inline-block;
width: 100%;
}
#app .bt .type {
  font-weight: bold;
  font-size: 20px;
  display: inline-block;
  text-transform: capitalize;
  background-color: rgba(0,0,0, 0.4);
  padding: 15px;
  color: white;
}
#app .bt .type span {
font-size: 12px;
}
#app .bt .list {
  list-style-type: none;
  margin: 0;
  padding: 0;
  width: 100%;
}
#app .bt .list li {
  padding: 20px;
  display: flex;
}

#app .bt .list li div {
  display: flex;
  justify-content: center;
  align-items: center;
}
#app .bt .list li div.icon {
  width: 40px;
  height: 50px;
  background-color: red;
}
#app .bt .list li div.name {
  font-size: 18px;
  font-weight: bold;
  padding-left: 15px;
  padding-right: 10px;
}

</style>