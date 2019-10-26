# -*-coding:utf-8-*-

from sklearn.metrics import confusion_matrix
import matplotlib.pyplot as plt
import numpy as np
from sklearn.utils.multiclass import unique_labels

# translate true y
y = []
with open('./testdata_y.txt') as f:
	for line in f:
		y.append((line.index('1')-1)//3)

y = np.array(y)
predict_y = np.loadtxt('./predict_result.txt')
labels = ["Safe","CWE-78","CWE-79","CWE-89","CWE-90","CWE-91","CWE-95","CWE-98","CWE-601","CWE-862",]

classes = np.array(labels)



def plot_confusion_matrix(y_true, y_pred, classes,
						  normalize=False,
						  title=None,
						  cmap=plt.cm.Blues):
	"""
	This function prints and plots the confusion matrix.
	Normalization can be applied by setting `normalize=True`.
	"""
	if not title:
		if normalize:
			title = 'Normalized confusion matrix'
		else:
			title = 'Confusion matrix, without normalization'

	# Compute confusion matrix
	cm = confusion_matrix(y_true, y_pred)
	# Only use the labels that appear in the data
	classes = classes[unique_labels(y_true, y_pred)]
	if normalize:
		cm = cm.astype('float') / cm.sum(axis=1)[:, np.newaxis]
		print("Normalized confusion matrix")
	else:
		print('Confusion matrix, without normalization')

	# print(cm.sum(axis=1)[:, np.newaxis])
	print(cm)

	fig, ax = plt.subplots()
	im = ax.imshow(cm, interpolation='nearest', cmap=cmap)
	ax.figure.colorbar(im, ax=ax)
	# We want to show all ticks...
	ax.set(xticks=np.arange(cm.shape[1]),
		   yticks=np.arange(cm.shape[0]),
		   # ... and label them with the respective list entries
		   xticklabels=classes, yticklabels=classes,
		   # title=title,
		   ylabel='True label',
		   xlabel='Predicted label')

	# Rotate the tick labels and set their alignment.
	plt.setp(ax.get_xticklabels(), rotation=45, ha="right",
			 rotation_mode="anchor")

	# Loop over data dimensions and create text annotations.
	fmt = '.2f' if normalize else 'd'
	thresh = cm.max() / 2.
	
	print(cm.sum(axis=1))
	
	for i in range(cm.shape[0]):
		for j in range(cm.shape[1]):
			ax.text(j, i, format(cm[i, j], fmt),
					ha="center", va="center",
					color="white" if cm[i, j] > thresh else "black")
	fig.tight_layout()
	return ax


plot_confusion_matrix(y,predict_y,classes, normalize=True,title="Normalized confusion matrix of TAP")
plt.savefig('tapcmNor.png',dpi=300)
plt.show()

from sklearn.metrics import cohen_kappa_score
kappa = cohen_kappa_score(y,predict_y)
print("Kappa: ",kappa)

from sklearn.metrics import hamming_loss
ham_distance = hamming_loss(y,predict_y)
print("ham_distance: ",ham_distance)


from sklearn.metrics import jaccard_similarity_score
jaccrd_score = jaccard_similarity_score(y,predict_y,normalize = True)
print("jaccrd_score: ",jaccrd_score)

