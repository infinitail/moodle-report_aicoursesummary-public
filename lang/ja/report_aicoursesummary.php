<?php
$string['questionpromptintrodefaultvalue'] = '次の問題文を解くために、主にどのような知識が必要か列挙してください。簡単にリストとして列挙してみてください。';
$string['studentprompt'] = '学生プロンプト';
$string['studentpromptintrodefaultvalue'] = '次の学生のクラスでの小テストの成績から、学生に対して提示するフィードバックコメントを500文字程度で作成してください。'
    .'学生の得意・不得意を意識して、今後につながるようなコメントをしてください。対象学生に対して言及する際は、必ず二人称を用いてください。'
    .'なお、全ての得点は0から1の間の値をとる得点率で表され、"-"の場合は未受験となります。'
    .'単純な得点率だけでなく、他の学生と比較して、出来が良かったかどうかについても考慮してください。';
$string['loop'] = 'ループ';
$string['studentpromptloopdefaultvalue'] = '{$a->qname}の第{$a->qcounter}問です。この問題は以下について理解しているかを問う問題です。'.PHP_EOL
    .'{$a->aiqresponse}'.PHP_EOL
    .'このクラスの全ての学生の得点率は、{$a->scoredistribution}でした。'.PHP_EOL
    .'この学生の得点率は、{$a->qscore}でした。';
