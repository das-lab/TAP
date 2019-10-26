#coding = utf-8

import os,shutil

safefilepath = "./safe_samples"
unsafefilepath = "./unsafe_samples"
sum = 0
numsafe = 0
numunsafe = 0


try:
    os.mkdir(safefilepath)
    os.mkdir(unsafefilepath)
except:
    pass

for root, dirs, files in os.walk("./testcases/000/"):
    for file in files:
        filepath = root+"/"+file
        sum += 1
        with open(filepath,'r') as f:
            for line in f.readlines()[:7]:
                if "Unsafe sample" in line:
                    shutil.copyfile(filepath,unsafefilepath+"/"+file)
                    numunsafe += 1
                    break
                elif "Safe sample" in line:
                    shutil.copyfile(filepath, safefilepath+"/"+file)
                    numsafe += 1
                    break
                # elif:
                #     print "Unknown file ",filepath


print('Total:',sum,'\n\rSafe sample:',numsafe,'\n\rUnsafe sample:',numunsafe)

