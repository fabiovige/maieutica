export function getKidPhotoUrl(photo) {
  if (photo) {
    return `/images/kids/${photo}`
  }

  return `/images/kids/default.png`
}
