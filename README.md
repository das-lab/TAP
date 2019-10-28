# TAP Demo

TAP: A Static Analysis Model for PHP Vulnerabilities Based on Token and Deep Learning Technology


Data download from
```
https://samate.nist.gov/SARD/index.php
```

Unzip 
```
SARD-testsuite-103.zip
```
 
Classify samples.
```
python classify.py
```

Correct some errors of SARD manually. 
![](./sarderrors.png) 

Get tokens by our tokenizer.
```
php Tokenizer.php
```

Run TAP.
```
python tap.py
```

Evaluation.
```
python confusion_matrix.py
```

Result.
![](./result/tapcmNor.png)
