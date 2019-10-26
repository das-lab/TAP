#coding=utf-8

import imp
import sys

imp.reload(sys)
import numpy as np
# import pandas as pd
import re
from gensim.models import Word2Vec
from keras.preprocessing import sequence
from gensim.corpora.dictionary import Dictionary
import multiprocessing
from sklearn.model_selection import train_test_split
import yaml
from keras.models import Sequential
from keras.layers.embeddings import Embedding
from keras.layers.recurrent import LSTM
from keras.layers.core import Dense, Dropout, Activation, Flatten, Masking
from keras.models import model_from_yaml
from keras.optimizers import Adam
from keras.layers import Conv1D,MaxPool1D
from gensim.models.word2vec import LineSentence
from keras import backend as K
from keras.layers import Bidirectional

K.clear_session()

np.random.seed(1337)  # For Reproducibility
# the dimension of word vector
vocab_dim = 256
# sentence length
maxlen = 350
# iter num
n_iterations = 1
# the number of words appearing
n_exposures = 1
# what is the maximum distance between the current word and the prediction word in a sentence, what is the maximum distance between the current and the prediction word in a sentence
window_size = 20
# batch size
batch_size = 512
# epoch num
n_epoch = 60
# input length
input_length = 350
# multi processing cpu number
cpu_count = multiprocessing.cpu_count()

labels = ["safe","CWE-78","CWE-79","CWE-89","CWE-90","CWE-91","CWE-95","CWE-98","CWE-601","CWE-862",]


def combine(safeFile,unsafeFile,unsafeYFile):
	global labels
	with open(safeFile,'r') as f:
		safe_tokens = f.readlines()
	with open(unsafeFile,'r') as f:
		unsafe_tokens = f.readlines()       
	combined = np.concatenate((safe_tokens,unsafe_tokens))
	# generating label and meging label data
	
	with open(unsafeYFile, 'r') as f:
		unsafe_labels = f.readlines()

	def tran_label(label):
		y_oh = np.zeros(10)
		y_oh[labels.index(label)] = 1
		return y_oh

	y = np.concatenate((np.array([tran_label(i.strip()) for i in unsafe_labels]), np.array([tran_label("safe") for i in safe_tokens])))
	return combined,y


# create a dictionary of words and phrases,return the index of each word,vector of words,and index of words corresponding to each sentence
def create_dictionaries(model=None,	combined=None):
	''' Function does are number of Jobs:
		1- Creates a word to index mapping
		2- Creates a word to vector mapping
		3- Transforms the Training and Testing Dictionaries
	'''
	if (combined is not None) and (model is not None):
		gensim_dict = Dictionary()
		gensim_dict.doc2bow(model.wv.vocab.keys(),
							allow_update=True)		
		# the index of a word which have word vector is not 0
		w2indx = {v: k + 1 for k, v in gensim_dict.items()}
		# integrate all the corresponding word vectors into the word vector matrix
		w2vec = {word: model[word] for word in w2indx.keys()}

		# a word without a word vector is indexed 0,return the index of word
		def parse_dataset(combined):
			''' Words become integers
			'''
			data = []
			for sentence in combined:
				new_txt = []
				# words = sentence.split()
				for word in sentence:
					try:
						new_txt.append(w2indx[word])
					except:
						new_txt.append(0)
				data.append(new_txt)
			return data

		combined = parse_dataset(combined)
		# unify the length of the sentence with the pad_sequences function of keras
		combined = sequence.pad_sequences(combined, maxlen=maxlen)
		# return index, word vector matrix and the sentence with an unifying length and indexed
		return w2indx, w2vec, combined
	else:
		print('No data provided...')


# the training of the word vector
def word2vec_train(combined):
	model = Word2Vec(size=vocab_dim,
					 min_count=n_exposures,
					 window=window_size,
					 workers=cpu_count,
					 iter=n_iterations)
	# build the vocabulary dictionary
	sentences=LineSentence('./traindata_x.txt')
	model.build_vocab(sentences)
	# train the word vector model
	model.train(sentences, total_examples=model.corpus_count, epochs=50)
	# save the trained model
	model.save('./Word2vec_model.pkl')
	# index, word vector matrix and the sentence with an unifying length and indexed based on the trained model
	
	data = []
	for sentence in combined:
		words = sentence.split()
		data.append(words)
		
	index_dict, word_vectors, combined = create_dictionaries(model=model, combined=data)

	return index_dict, word_vectors, combined


def get_data(index_dict, word_vectors, combined, y):
	# total number of word including the word without word vector
	n_symbols = len(index_dict) + 1
	# build word vector matrix which corresponding to the word index one by one
	embedding_weights = np.zeros((n_symbols, vocab_dim))
	for word, index in index_dict.items():  
		embedding_weights[index, :] = word_vectors[word]
	# partition test set and training set
	x_train, x_validate, y_train, y_validate = train_test_split(combined, y, test_size=0.125)
	#print(x_train.shape, y_train.shape)
	# return the input parameters needed of the lstm model
	return n_symbols, embedding_weights, x_train, y_train, x_validate, y_validate


##定义网络结构
def train_lstm(n_symbols, embedding_weights, x_train, y_train, x_validate, y_validate):
	print('Defining a Simple Keras Model...')
	model = Sequential()  # or Graph or whatever

	model.add(Embedding(output_dim=vocab_dim,
						input_dim=n_symbols,
						mask_zero=True,
						weights=[embedding_weights],
						input_length=input_length))  # Adding Input Length


	model.add(LSTM(256))
	model.add(Dropout(0.5))
	model.add(Dense(10,activation='softmax'))


	print ('Compiling the Model...')
	adam = Adam(lr=0.0001)
	model.compile(loss='categorical_crossentropy',
				  optimizer=adam,
				  metrics=['accuracy'])
	model.summary()
	print ("Train...")

	model.fit(x_train, y_train, batch_size=batch_size, epochs=n_epoch, verbose=2,shuffle=True,class_weight='balanced', validation_data=(x_validate, y_validate))

	print ("Evaluate...")
	score = model.evaluate(x_validate, y_validate,
						   batch_size=batch_size)
	# save the trained lstm model
	yaml_string = model.to_yaml()
	with open('./lstm.yml', 'w') as outfile:
		outfile.write(yaml.dump(yaml_string, default_flow_style=True))
	model.save_weights('./lstm.h5')
	print ('Test score:', score)


# 训练模型，并保存
def train():
	print('Tokenising...')
	# load data
	safeFile = './safe_tokens.txt'
	unsafeFile = './unsafe_tokens.txt'
	unsafeYFile = './unsafe_y.txt'
	combined_x,combined_y = combine(safeFile,unsafeFile,unsafeYFile)	
	
	x_train_validate, x_test, y_train_validate, y_test = train_test_split(combined_x,combined_y,test_size=0.2)
	
	with open('./testdata_x.txt','w') as f:
		for i in x_test:
			f.write(i)
	with open('./testdata_y.txt','w') as f:
		for i in y_test:
			f.write(str(i))
			f.write('\n')
	with open('./traindata_x.txt','w') as f:
		for i in x_train_validate:
			f.write(i)
	with open('./traindata_y.txt','w') as f:
		for i in y_train_validate:
			f.write(str(i))
			f.write('\n')		
			
			
			
	print('Total: ',len(x_train_validate)+len(x_test),len(y_train_validate)+len(y_test))
	print('Train & Validate :',len(x_train_validate), len(y_train_validate))
	print('Test: ',len(x_test),len(y_test))

	print('Training a Word2vec model...')
	index_dict, word_vectors, x_train_validate = word2vec_train(x_train_validate)


	print('Setting up Arrays for Keras Embedding Layer...')
	# n_symbols, embedding_weights, x_train, y_train, x_test, y_test = get_data(index_dict, word_vectors, combined, y)
	n_symbols, embedding_weights, x_train, y_train, x_validate, y_validate = get_data(index_dict, word_vectors, x_train_validate, y_train_validate)
	print(x_train.shape, y_train.shape)
	
		
	# echo results of each epoch
	data = []
	for sentence in x_test:
		words = sentence.split()
		data.append(words)
	model = Word2Vec.load('./Word2vec_model.pkl')
	_, _, x_test = create_dictionaries(model=model, combined=data)
	
	train_lstm(n_symbols, embedding_weights, x_train, y_train, x_validate, y_validate)

# building the input format data
def input_transform(string):
	# reshape the list to bilayer list
	words = string.split()
	words = np.array(words).reshape(1, -1)
	model = Word2Vec.load('./Word2vec_model.pkl')
	# create a dictionary of words and phrases,return the index of each word,vector of words,and index of words corresponding to each senten
	_, _, combined = create_dictionaries(model, words)
	return combined
	
def lstm_predict():
	global labels
	print('loading model......')
	with open('./lstm.yml', 'r') as f:
		yaml_string = yaml.load(f)
	model = model_from_yaml(yaml_string)

	print('loading weights......')
	model.load_weights('./lstm.h5')
	with open('./testdata_x.txt','r') as f:
		strings = f.readlines()

	
	with open('./testdata_y.txt','r') as f:
		y = f.readlines()
		
	i = 0
	right = 0
	false = 0
	prevalue = ''
	preresult = ''
	for string in strings:
		data = input_transform(string)
		result = model.predict_classes(data)[0]
		value = model.predict(data)[0]
		prevalue += ((','.join(str(i) for i in value))+'\n')
		# preresult += (labels[result]+'\n')
		preresult += (str(result)+'\n')
		
		t = (1+result*3)
		if 1 == int(y[i][t:t+1]):
			right += 1
		else:
			false += 1
			
		i += 1
		
	with open('./predict_value.txt','w') as f:
		f.write(prevalue)
	with open('./predict_result.txt','w') as f:
		f.write(preresult)
		
		
	print('right: ',right,' false: ',false)
	print('accuracy: ',right/(right+false))
		
if __name__ == '__main__':
	train()

	lstm_predict()
