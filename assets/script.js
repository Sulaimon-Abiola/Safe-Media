;(function ($) {
	const { currentScreen } = smd_helper_object

	const smdTemplate = function (view) {
		var html =
			currentScreen && currentScreen?.post_type && currentScreen?.post_type === 'attachment'
				? wp.media.template('attachment-details-two-column')(view)
				: wp.media.template('attachment-details')(view)

		const dom = document.createElement('div')
		dom.innerHTML = html

		const caption = dom.querySelector(`[data-setting="caption"]`)

		const linkedObjects = document.createElement('span')
		linkedObjects.classList.add('setting')
		linkedObjects.style.display = 'flex'
		linkedObjects.style.alignItems = 'center'

		linkedObjects.innerHTML = `
            <label for="attachment-details-linked-objects" class="name">Linked objects</label>
            <div id="attachment-details-linked-objects">loading...</div>
         `

		caption.after(linkedObjects)

		getAttachmentDataById(this.model.attributes.id)
			.then((linkedObjects) => {
				let linkedObjectText = ''

				for (const [_, value] of Object.entries(linkedObjects?.linkedObjects)) {
					linkedObjectText += value + ', '
				}

				document.getElementById('attachment-details-linked-objects').innerHTML =
					linkedObjectText.slice(0, -2) || '_'
			})
			.catch((error) => {
				document.getElementById(
					'attachment-details-linked-objects'
				).innerText = `There was an error loading the linked objects, please try again`
			})

		return dom.innerHTML
	}

	const smdDeleteAttachment = function (evt) {
		evt.preventDefault()

		this.getFocusableElements()

		if (window.confirm(wp.media.view.l10n.warnDelete)) {
			this.model.destroy({
				wait: true,
				error: function (_, err) {
					$('body').append(`
                  <div class="smd-media-alert">
                     <div class="smd-media-alert__close">Close</div>
                     <div class="smd-media-alert__inner">
                        ${err?.responseJSON?.data}   
                     </div>
                  </div>
               `)
				},
			})

			this.moveFocus()
		}
	}

	async function getAttachmentDataById(id) {
		try {
			const attachementRes = await fetch(
				`${smd_helper_object.siteUrl}/wp-json/assignment/v1/attachments/${id}`
			)

			const attachment = await attachementRes.json()

			return {
				success: true,
				linkedObjects: attachment?.attachedObjects,
			}
		} catch (error) {
			return {
				success: false,
				linkedObjects: [],
			}
		}
	}

	var TwoColumn = wp.media.view.Attachment.Details.TwoColumn

	wp.media.view.Attachment.Details.TwoColumn = TwoColumn?.extend({
		template: smdTemplate,
		deleteAttachment: smdDeleteAttachment,
	})

	wp.media.view.Attachment.Details.prototype.template = smdTemplate
	wp.media.view.Attachment.Details.prototype.deleteAttachment = smdDeleteAttachment

	$(document).on('click', '.smd-media-alert__close', function () {
		$('.smd-media-alert').remove()
	})
})(jQuery)
