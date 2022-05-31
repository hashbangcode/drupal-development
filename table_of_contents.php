<?php

$slidesFile = './src/slides.md';
$fileContents = file_get_contents($slidesFile);

$slides = explode("---" . PHP_EOL, $fileContents);

$tocArray = [];

$toc = '';

$toc .= '<!-- _footer: "" -->' . PHP_EOL;

$toc .=  PHP_EOL . '## Contents' . PHP_EOL . PHP_EOL;

$toc .= "<style>
.container {
    display: flex;
    font-size: 0.8rem;
}
.col {
    flex: 1;
}
</style>";

$toc .= PHP_EOL . '<div class="container">' . PHP_EOL . PHP_EOL;

$bulletCount = 0;

foreach ($slides as $id => $slide) {
    if (preg_match('/^#{1}\s(.*)/m', $slide, $matches)) {
        if ($bulletCount == 0) {
            $toc .= '<div class="col">' . PHP_EOL;
        }
        $toc .= PHP_EOL . '- [' . $matches[1] . '](#' . ($id-2) . ')';
        $bulletCount++;
        if ($bulletCount == 10) {
            $bulletCount = 0;
            $toc .= PHP_EOL . PHP_EOL . '</div>' . PHP_EOL . PHP_EOL;
        }
    }
}

$toc .= PHP_EOL . PHP_EOL . '</div>';
$toc .= PHP_EOL . '</div>' . PHP_EOL . PHP_EOL;

$slides[4] = $toc;
$slides = trim(implode("---\n", $slides));

file_put_contents($slidesFile, $slides);

echo 'done.';