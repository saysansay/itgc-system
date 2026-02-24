import Swal from 'sweetalert2'

export const confirmAction = async (message, title = 'Are you sure?', options = {}) => {
  const {
    confirmButtonText = 'Yes',
    cancelButtonText = 'Cancel',
    confirmButtonColor = '#0f6cbd',
    icon = 'warning'
  } = options
  const result = await Swal.fire({
    title,
    text: message,
    icon,
    showCancelButton: true,
    confirmButtonText,
    cancelButtonText,
    confirmButtonColor
  })

  return result.isConfirmed
}

export const notifySuccess = (message) => Swal.fire({
  icon: 'success',
  title: message,
  timer: 1500,
  showConfirmButton: false
})

export const notifyError = (message) => Swal.fire({
  icon: 'error',
  title: 'Error',
  text: message
})

export const notifyInfo = (message) => Swal.fire({
  icon: 'info',
  title: message
})
