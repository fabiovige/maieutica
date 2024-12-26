// utils/photoUtils.js
export function getKidPhotoUrl(photo) {
    if (photo) {
        return `/storage/${photo}`;
    }

    const randomAvatarNumber = Math.floor(Math.random() * 13) + 1;
    return `/storage/kids_avatars/avatar${randomAvatarNumber}.png`;
}
