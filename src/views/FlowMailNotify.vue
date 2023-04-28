<template>
  <!--
    SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
    SPDX-License-Identifier: AGPL-3.0-or-later
    -->
<div>
<div>
		<input type="text"
			maxlength="80"
			:placeholder="placeholder"
			:value="currentSubject"
			@input="emitSubjectInput">
	</div>
	<div>
		<input type="text"
			maxlength="80"
			:placeholder="placeholderFrom"
			:value="currentFrom"
			@input="emitFromInput">
	</div>
	<div>
		<input type="text"
			maxlength="80"
			:placeholder="placeholderTo"
			:value="currentTo"
			@input="emitToInput">
	</div>
	<div>
		<input type="hidden"
			id="inputEditor"
			:value="currentMailto">
		<VueTrix  inputId="inputEditor" placeholder="E-mail content" :value="currentMailto" :disabledEditor="false" />
	</div>
</div>
	
</template>

<script>

/**
 * @copyright Copyright (c) 2020 Hervé de CHAVIGNY <vevedh@gmail.com>
 * @author Hervé de CHAVIGNY <vevedh@gmail.com>
 * @license AGPL-3.0-or-later
 */

import Vue from 'vue';
import VueTrix from "vue-trix";
Vue.use(VueTrix);

export default {
	name: 'FlowMailNotify',
	components: {
		VueTrix
	},
	props: {
		value: {
			default: JSON.stringify({ subject: '', from:'', to: '', mailcontent:'ex: fichier {filename} créer par {user} ' }),
			type: String,
		}
	},
	data() {
		return {
			subject: '',
			placeholder: t('mailnotifier', 'Choisissez l\'objet du mail'),
			from: '',
			placeholderFrom: t('mailnotifier', 'adresse-email@source.com,Nom Adresse Source'),
			to: '',
			placeholderTo: t('mailnotifier', 'Les adresses e-mails des destinataires (separateur \';\')'),
			mailcontent:'',
			trixEditor: null,
			trixContent:'',
		}
	},
	mounted() {
		if (document) {
			document.addEventListener("trix-initialize", (e) => {

				const self = this
				const trix = e.target
        		const toolBar = trix.toolbarElement
				this.trixEditor = e.target
				
		})
		document?.addEventListener('trix-change',(evt)=>{
					if (evt.returnValue) {
						console.log('Input selection :',evt)
						console.log('Input selection :',evt?.target?.value)
						try {
							this.trixContent = evt?.target?.value
						    //this.value = JSON.stringify({ subject: this.currentSubject , to: this.currentTo , mailcontent: this.trixContent})
							this.$emit('input', JSON.stringify({ subject: this.currentSubject , from:this.currentFrom, to: this.currentTo , mailcontent: this.trixContent}))
						} catch (e) {
							console.log('Not ready')
						}
					}
					
					
				})
		}
		
		
	},
	computed: {
		currentSubject() {
			if (!this.value) {
				return ''
			}
			return JSON.parse(this.value).subject
		},
		currentFrom() {
			if (!this.value) {
				return ''
			}
			return JSON.parse(this.value).from
		},
		currentTo() {
			if (!this.value) {
				return ''
			}
			return JSON.parse(this.value).to
		},
		currentMailto() {
			if (!this.value) {
				return ''
			}
			return JSON.parse(this.value).mailcontent
		}
	},
	methods: {
		emitSubjectInput(value) {
			if (value !== null) {
				
				this.$emit('input', JSON.stringify({ subject: value.target.value , from:this.currentFrom , to: this.currentTo , mailcontent: this.currentMailto}))
			}
		},
		emitToInput(value) {
			if (value !== null) {
				this.$emit('input', JSON.stringify({ subject: this.currentSubject  , from:this.currentFrom , to: value.target.value , mailcontent: this.currentMailto}))
			}
		},
		emitFromInput(value) {
			if (value !== null) {
				this.$emit('input', JSON.stringify({ subject: this.currentSubject  , from:value.target.value , to: this.currentTo , mailcontent: this.currentMailto}))
			}
		}

	}
}

</script>

<style scoped>
	input {
		width: 100% !important;
	}
	.actions__item_options {
		width: 100%;
		margin-top: 10px;
		/* padding-left: 60px; */
	}
</style>
